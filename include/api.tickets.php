<?php

include_once INCLUDE_DIR.'class.api.php';
include_once INCLUDE_DIR.'class.ticket.php';

class ApiException extends \Exception{} //There is probably an existing OSTicket Exception which should be used

class TicketApiController extends ApiController {

    # Supported arguments -- anything else is an error. These items will be
    # inspected _after_ the fixup() method of the ApiXxxDataParser classes
    # so that all supported input formats should be supported
    function getRequestStructure($format, $data=null) {
        $supported = array(
            "alert", "autorespond", "source", "topicId",
            "attachments" => array("*" =>
                array("name", "type", "data", "encoding", "size")
            ),
            "message", "ip", "priorityId"
        );
        # Fetch dynamic form field names for the given help topic and add
        # the names to the supported request structure
        if (isset($data['topicId'])
                && ($topic = Topic::lookup($data['topicId']))
                && ($forms = $topic->getForms())) {
            foreach ($forms as $form)
                foreach ($form->getDynamicFields() as $field)
                    $supported[] = $field->get('name');
        }

        # Ticket form fields
        # TODO: Support userId for existing user
        if(($form = TicketForm::getInstance()))
            foreach ($form->getFields() as $field)
                $supported[] = $field->get('name');

        # User form fields
        if(($form = UserForm::getInstance()))
            foreach ($form->getFields() as $field)
                $supported[] = $field->get('name');

        if(!strcasecmp($format, 'email')) {
            $supported = array_merge($supported, array('header', 'mid',
                'emailId', 'to-email-id', 'ticketId', 'reply-to', 'reply-to-name',
                'in-reply-to', 'references', 'thread-type',
                'mailflags' => array('bounce', 'auto-reply', 'spam', 'viral'),
                'recipients' => array('*' => array('name', 'email', 'source'))
                ));

            $supported['attachments']['*'][] = 'cid';
        }

        return $supported;
    }

    /*
     Validate data - overwrites parent's validator for additional validations.
    */
    function validate(&$data, $format, $strict=true) {
        global $ost;

        //Call parent to Validate the structure
        if(!parent::validate($data, $format, $strict) && $strict)
            $this->exerr(400, __('Unexpected or invalid data received'));

        // Use the settings on the thread entry on the ticket details
        // form to validate the attachments in the email
        $tform = TicketForm::objects()->one()->getForm();
        $messageField = $tform->getField('message');
        $fileField = $messageField->getWidget()->getAttachments();

        // Nuke attachments IF API files are not allowed.
        if (!$messageField->isAttachmentsEnabled())
            $data['attachments'] = array();

        //Validate attachments: Do error checking... soft fail - set the error and pass on the request.
        if ($data['attachments'] && is_array($data['attachments'])) {
            foreach($data['attachments'] as &$file) {
                if ($file['encoding'] && !strcasecmp($file['encoding'], 'base64')) {
                    if(!($file['data'] = base64_decode($file['data'], true)))
                        $file['error'] = sprintf(__('%s: Poorly encoded base64 data'),
                            Format::htmlchars($file['name']));
                }
                // Validate and save immediately
                try {
                    $F = $fileField->uploadAttachment($file);
                    $file['id'] = $F->getId();
                }
                catch (FileUploadError $ex) {
                    $file['error'] = $file['name'] . ': ' . $ex->getMessage();
                }
            }
            unset($file);
        }

        return true;
    }


    function create($format) {

        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $ticket = null;
        if(!strcasecmp($format, 'email')) {
            # Handle remote piped emails - could be a reply...etc.
            $ticket = $this->processEmail();
        } else {
            # Parse request body
            $ticket = $this->createTicket($this->getRequest($format));
        }

        if(!$ticket)
            return $this->exerr(500, __("Unable to create new ticket: unknown error"));

        $this->response(201, json_encode($ticket));
    }

    /* private helper functions */

    function createTicket($data) {

        # Pull off some meta-data
        $alert       = (bool) (isset($data['alert'])       ? $data['alert']       : true);
        $autorespond = (bool) (isset($data['autorespond']) ? $data['autorespond'] : true);

        # Assign default value to source if not defined, or defined as NULL
        $data['source'] = isset($data['source']) ? $data['source'] : 'API';

        # Create the ticket with the data (attempt to anyway)
        $errors = array();

        $ticket = Ticket::create($data, $errors, $data['source'], $autorespond, $alert);
        # Return errors (?)
        if (count($errors)) {
            if(isset($errors['errno']) && $errors['errno'] == 403)
                return $this->exerr(403, __('Ticket denied'));
            else
                return $this->exerr(
                        400,
                        __("Unable to create new ticket: validation errors").":\n"
                        .Format::array_implode(": ", "\n", $errors)
                        );
        } elseif (!$ticket) {
            return $this->exerr(500, __("Unable to create new ticket: unknown error"));
        }

        return $ticket;
    }

    function processEmail($data=false) {

        if (!$data)
            $data = $this->getEmailRequest();

        $seen = false;
        if (($entry = ThreadEntry::lookupByEmailHeaders($data, $seen))
            && ($message = $entry->postEmail($data))
        ) {
            if ($message instanceof ThreadEntry) {
                return $message->getThread()->getObject();
            }
            else if ($seen) {
                // Email has been processed previously
                return $entry->getThread()->getObject();
            }
        }

        // Allow continuation of thread without initial message or note
        elseif (($thread = Thread::lookupByEmailHeaders($data))
            && ($message = $thread->postEmail($data))
        ) {
            return $thread->getObject();
        }

        // All emails which do not appear to be part of an existing thread
        // will always create new "Tickets". All other objects will need to
        // be created via the web interface or the API
        return $this->createTicket($data);
    }

    ######  Added Methods ############
    //Added client methods to support API endpoints.  See https://github.com/osTicket/osTicket/pull/4361/files for staff methods.
    //All methods assume dispatcher validates arguments as integers and handles errors (i.e. (?P<tid>\d+) ).
    //All methods require client email in the parameters except for getTopics() and getTicket().  Email is not used for authentication, but to log who made a change.

    public function getTicket(string $format, int $tid):Response {
        //syslog(LOG_INFO, "TicketApiController::getTicket($tid) using $format");
        $api=$this->getApi(); //Should this be used.  Currently only fetched to validate API key.
        $ticket = $this->getByTicketId($tid);
        return $this->response(200, json_encode($ticket));
    }
    public function closeTicket(string $format, int $tid):Response {
        //syslog(LOG_INFO, "TicketApiController::closeTicket($tid) using $format");
        $api=$this->getApi(); //Should this be used.  Currently only fetched to validate API key.
        $ticket = $this->getByTicketId($tid, $this->getEmail($format));
        //$ticket->setStatusId(3);
        //$currentStatus=$ticket->getStatus();
        $status= TicketStatus::lookup(3);
        $errors=[];//passed by reference
        $ticket->setStatus($status, 'Closed by user', $errors);
        return $this->response(204, null);
    }
    public function reopenTicket(string $format, int $tid):Response {
        //syslog(LOG_INFO, "TicketApiController::closeTicket($tid) using $format");
        $api=$this->getApi(); //Should this be used.  Currently only fetched to validate API key.
        $ticket = $this->getByTicketId($tid, $this->getEmail($format));
        $ticket->reopen();
        return $this->response(200, json_encode($ticket));
    }
    public function updateTicket(string $format, int $tid):Response {
        //syslog(LOG_INFO, "TicketApiController::updateTicket($tid) using $format");    //.json_encode($_POST));
        $api=$this->getApi(); //Should this be used.  Currently only fetched to validate API key.
        $params = $this->getParams($format);
        $ticket = $this->getByTicketId($tid, $params['email']??false);
        $vars=[
            'message'=>$params['message']->getClean(),
            'files'=>[],    //How should this be implemented?
            'draft_id'=>'', //???
            'ip_address'=>$_SERVER['REMOTE_ADDR']
        ];
        $response = $ticket->postMessage($vars, 'web', true);
        return $this->response(200, json_encode($ticket));
    }
    public function getTickets(string $format):Response {
        //Future:  Allow for optional filtering for name and topic ID
        //syslog(LOG_INFO, "TicketApiController::getTickets() using $format");
        $api=$this->getApi(); //Should this be used.  Currently only fetched to validate API key.
        if(!$user = TicketUser::lookupByEmail($this->getEmail($format))) {
            return $this->exerr(400, __('Invalid user'));
        }
        $tickets = Ticket::objects()->filter(array('user_id' => $user->getId()))->all();
        return $this->response(200, json_encode($this->createList($tickets, 'id', 'value')));
    }
    public function getTopics(string $format):Response {
        //syslog(LOG_INFO, "TicketApiController::getTopics() using $format");
        //The one exception where client email is not required.
        $api=$this->getApi(); //Should this be used.  Currently only fetched to validate API key.
        $topics=Topic::getPublicHelpTopics();
        return $this->response(200, json_encode($this->createList($topics, 'id', 'value')));
    }

    // Private methods to support new api methods.  Verify if existing osticket methods should be used instead.
    private function getByTicketId(int $ticketId, string $email=null):Ticket {
        if(!$pk=Ticket::getIdByNumber($ticketId)){
            throw new ApiException('Unknown or invalid ticket ID.', 400);
        }
        return $this->getByPrimaryId($pk, $email);
    }
    private function getByPrimaryId(int $pk, $email):Ticket {
        if(is_null($email)) {
            //If null is based for $email, don't verify authority (will be for viewing only).  Maybe change for any GET requests instead?
            if(!$ticket = Ticket::lookup($pk)) {
                throw new ApiException('Unknown or invalid ticket ID.', 400);
            }
        }
        else {
            //$email is not used to authenticate user's access privilage since the api key is used instead, but is used to update who made the change.
            if(empty($email)) {
                throw new ApiException('All API requests must pass email as a parameter', 400);
            }
            if(!$user = TicketUser::lookupByEmail($email)) {
                throw new ApiException('Invalid user', 400);
            }
            if(!$ticket = Ticket::lookup($pk)) {
                throw new ApiException('Unknown or invalid ticket ID.', 400);
            }
            if(!$ticket->checkUserAccess($user)) {
                throw new ApiException('Unknown or invalid ticket ID.', 400); //Using generic message on purpose!
            }
        }
        return $ticket;
    }
    private function getEmail(string $format):string {
        $params=$this->getParams($format);
        if(empty($params['email'])){
            throw new ApiException('Missing value for email.', 400);
        }
        return $params['email'];
    }
    private function getParams(string $format):array {
        return in_array($_SERVER['REQUEST_METHOD'], ['GET'])?$_GET:$this->getRequest($format);
    }
    private function getApi($create=false):API {
        if(!($api=$this->requireApiKey()) || $create && !$api->canCreateTickets()) {
            throw new ApiException('API key not authorized.', 401);
        }
        return $api;
    }
    private function createList(array $items, string $idName, string $valueName):array {
        $list=[];
        foreach($items as $key=>$value) {
            $list[]=[$idName=>$key, $valueName=>$value];
        }
        return $list;
    }


    //The following methods were provided by https://github.com/osTicket/osTicket/pull/4361/commits/781e15b0dd89c205d3999fb844e984b695a36368 and I have not tested
    //Recommend making TicketApiController abstract and adding TicketApiClientController and TicketApiStaffController

    public function getTicketInfo() {
        try{
            if(!($key=$this->requireApiKey()))
                return $this->exerr(401, __('API key not authorized'));


            $ticket_number = $_REQUEST['ticketNumber'];
            if (! ($ticket_number))
                return $this->exerr(422, __('missing ticketNumber parameter '));

            # Checks for valid ticket number
            if (!is_numeric($ticket_number))
                return $this->response(404, __("Invalid ticket number"));



            # Checks for existing ticket with that number
            $id = Ticket::getIdByNumber($ticket_number);


            if ($id <= 0)
                return $this->response(404, __("Ticket not found"));
            # Load ticket and send response
            $ticket = new Ticket(0);
            //$ticket->load($id);
            $ticket=Ticket::lookup($id);

            $result =  array('ticket'=> $ticket ,'status_code' => '0', 'status_msg' => 'ticket details retrieved successfully');
            $result_code=200;
            $this->response($result_code, json_encode($result ),
                $contentType="application/json");

        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('ticket'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response(500, json_encode($result),
                $contentType="application/json");
        }
    }
    /**
    * RESTful GET ticket collection
    *
    * Pagination is made wit Range header.
    * i.e.
    *      Range: items=0-    <-- request all items
    *      Range: items=0-9   <-- request first 10 items
    *      Range: items 10-19 <-- request items 11 to 20
    *
    * Pagination status is given on Content-Range header.
    * i.e.
    *      Content-Range items 0-9/100 <-- first 10 items retrieved, 100 total items.
    *
    * TODO: Add filtering support
    *
    * NOT WORKING?
    */
    public function restGetTickets() {
        if(!($key=$this->requireApiKey()))
            return $this->exerr(401, __('API key not authorized'));
        # Build query
        $qfields = array('number', 'created', 'updated', 'closed');
        $q = 'SELECT ';
        foreach ($qfields as $f) {
            $q.=$f.',';
        }
        $q=rtrim($q, ',');
        $qfrom = ' FROM '.TICKET_TABLE;
        $q .= $qfrom;
        $res = db_query($q);
        header("TEST:".$q);
        mysqli_free_result($res2);
        unset($row);
        $tickets = array();
        $result_rows = $res->num_rows ;
        // header("rowNum :  ${result_rows}");
        for ($row_no = 0; $row_no < $result_rows; $row_no++) {
            $res->data_seek($row_no);
            $row = $res->fetch_assoc();
            $ticket = array();
            foreach ($qfields as $f) {
                array_push($ticket, array($f, $row[$f]));
            }
            array_push($ticket, array('href', '/api/tickets/'.$row['number']));
            array_push($tickets, $ticket);
        }

        $result_code = 200;
        $this->response($result_code, json_encode($tickets), $contentType = "application/json");
    }
    // staff tickets
    public function getStaffTickets()
    {
        try{
            if (! ($key = $this->requireApiKey()))
                return $this->exerr(401, __('API key not authorized'));

            $staffUserName = $_REQUEST['staffUserName'];
            if (! ($staffUserName))
                return $this->exerr(422, __('missing staffUserName parameter '));
            mysqli_set_charset('utf8mb4');
            $staff = Staff::lookup(array(
                'username' => $staffUserName
            ));

            $myTickets = Ticket::objects()->filter(array(
                'staff_id' => $staff->getId()
            ))
            ->all();

            $tickets = array();
            foreach ($myTickets as $ticket) {
                array_push($tickets, $ticket);
            }

            $result_code = 200;
            $result =  array('tickets'=> $tickets ,'status_code' => '0', 'status_msg' => 'success');
            $this->response($result_code, json_encode($result),
                $contentType="application/json");

        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('tickets'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response($result_code, json_encode($result),
                $contentType="application/json");
        }
    }

    //client tickets
    public function getClientTickets() {
        try{
            if(!($key=$this->requireApiKey()))
                return $this->exerr(401, __('API key not authorized'));
            mysqli_set_charset('utf8mb4');

            $clientUserName = $_REQUEST['clientUserMail'];
            if(!($clientUserName))
                return $this->exerr(422, __('missing clientUserMail parameter '));
            $user = TicketUser::lookupByEmail($clientUserName);

            $myTickets = Ticket::objects()->filter(array('user_id' => $user->getId()))->all();

            $tickets = array();
            foreach ($myTickets as $ticket) {
                array_push($tickets, $ticket);
            }

            $result_code = 200;
            $result =  array('tickets'=> $tickets ,'status_code' => '0', 'status_msg' => 'success');

            $this->response($result_code, json_encode($result ),
                $contentType="application/json");

        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('tickets'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response(500, json_encode($result),
                $contentType="application/json");
        }
    }

    //staff replies to client ticket with the updated status
    public function postReply($format) {
        try{
            if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
                return $this->exerr(401, __('API key not authorized'));

            $data = $this->getRequest($format);

            # Checks for existing ticket with that number
            $id = Ticket::getIdByNumber($data['ticketNumber']);
            if ($id <= 0)
                return $this->response(404, __("Ticket not found"));

            $data['id']=$id;
            $staff = Staff::lookup(array('username'=>$data['staffUserName']));
            $data['staffId']= $staff -> getId();
            $data['poster'] = $staff;

            $ticket=Ticket::lookup($id);
            $errors = array();
            $response = $ticket->postReply($data , $errors);

            if(!$response)
                return $this->exerr(500, __("Unable to reply to this ticket: unknown error"));

            $location_base = '/api/tickets/';
            // header('Location: '.$location_base.$ticket->getNumber());
            // $this->response(201, $ticket->getNumber());
            $result =  array( 'status_code' => '0', 'status_msg' => 'reply posted successfully');
            $result_code=200;
            $this->response($result_code, json_encode($result ),
                $contentType="application/json");
        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('tickets'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response(500, json_encode($result),
                $contentType="application/json");
        }
    }
}

//Local email piping controller - no API key required!
class PipeApiController extends TicketApiController {

    //Overwrite grandparent's (ApiController) response method.
    function response($code, $resp) {

        //Use postfix exit codes - instead of HTTP
        switch($code) {
            case 201: //Success
                $exitcode = 0;
                break;
            case 400:
                $exitcode = 66;
                break;
            case 401: /* permission denied */
            case 403:
                $exitcode = 77;
                break;
            case 415:
            case 416:
            case 417:
            case 501:
                $exitcode = 65;
                break;
            case 503:
                $exitcode = 69;
                break;
            case 500: //Server error.
            default: //Temp (unknown) failure - retry
                $exitcode = 75;
        }

        //echo "$code ($exitcode):$resp";
        //We're simply exiting - MTA will take care of the rest based on exit code!
        exit($exitcode);
    }

    function  process() {
        $pipe = new PipeApiController();
        if(($ticket=$pipe->processEmail()))
           return $pipe->response(201, $ticket->getNumber());

        return $pipe->exerr(416, __('Request failed - retry again!'));
    }
}

?>
