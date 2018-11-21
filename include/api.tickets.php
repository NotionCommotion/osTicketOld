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
    //All methods assume dispatcher validates arguments as integers and handles errors (i.e. (?P<tid>\d+) ).
    //Most tickets require userId or email in paramaters (not necessarily for authentication, but to log who made a change)

    //Maybe change creating new osTicket not to require name?
    public function create($format) {
        $this->validatePermision(true); //will throw exception if invalid
        $ticket = null;
        if(!strcasecmp($format, 'email')) {
            # Handle remote piped emails - could be a reply...etc.
            $ticket = $this->processEmail();
        } else {
            # Parse request body
            $params = $this->getParams($format);
            if(!isset($params['email'])) {
                $params['email']=$this->getUser($format, $params)->getEmail();
            }
            $params['source']='api';
            /* The standard web interface provides the first (10) properties to Ticket::create() and the entire object to Ticket::postMessage()
            {
                "__CSRFToken__": "89921b66f3542e3e0b01b41727fb172d75060725",
                "a": "open",
                "topicId": "1",
                "c95bc232fc241ae7": "My Summary from SC",
                "message": "My Message",
                "draft_id": "",
                "emailId": 0,
                "deptId": 0,
                "uid": 35,
                "cannedattachments": [],
                "field.20": "My Summary from SC",
                "field.21": "My Message",
                "field.4": "Added by admin create Greenbean user.",
                "field.2": {
                    "format": "original",
                    "parts": {
                        "salutation": "",
                        "first": "Michael",
                        "suffix": "",
                        "last": "Reed",
                        "middle": ""
                    },
                    "name": "Michael Reed"
                },
                "field.1": {
                    "address": "villascape@gmail.com"
                },
                "email": {
                    "address": "villascape@gmail.com"
                },
                "name": "Michael Reed",
                "title": null,
                "userId": 35
            }
            The My API provides the first (7) properties to Ticket::create() and the entire object to Ticket::postMessage()
            {
                "userId": 35,
                "name": "Michael Reed",
                "topicId": "1",
                "subject": "My subject from API",
                "message": "My message",
                "email": {
                    "address": "villascape@gmail.com"
                },
                "source": "api",
                "field.20": "My subject from API",
                "field.21": "My message",
                "field.2": {
                    "format": "original",
                    "parts": {
                        "salutation": "",
                        "first": "Michael",
                        "suffix": "",
                        "last": "Reed",
                        "middle": ""
                    },
                    "name": "Michael Reed"
                },
                "field.4": "Added by admin create Greenbean user.",
                "field.1": {
                    "address": "villascape@gmail.com"
                },
                "title": "My subject from API"
            }
            */
            $ticket = $this->createTicket($params);
        }

        if(!$ticket) {
            throw new ApiException('Unable to create new ticket: unknown error.', 500);
        }
        $this->response(201, $ticket);
    }

    //Added client methods to support API endpoints.
    public function getTicket(string $format, int $tid):Response {
        //This API request does not need to provide user identifier.
        $this->validatePermision(); //will throw exception if invalid
        $ticket = $this->getByTicketId($tid);
        return $this->response(200, $ticket);
    }
    public function closeTicket(string $format, int $tid):Response {
        //syslog(LOG_INFO, "TicketApiController::closeTicket($tid) using $format");
        $this->validatePermision(true); //will throw exception if invalid
        $ticket = $this->getByTicketId($tid, $this->getUser($format));
        //$ticket->setStatusId(3);
        //$currentStatus=$ticket->getStatus();
        $status= TicketStatus::lookup(3);
        $errors=[];//passed by reference
        $ticket->setStatus($status, 'Closed by user', $errors);
        return $this->response(204, null);
    }
    public function reopenTicket(string $format, int $tid):Response {
        $this->validatePermision(true); //will throw exception if invalid
        $ticket = $this->getByTicketId($tid, $this->getUser($format));
        $ticket->reopen();
        return $this->response(200, $ticket);
    }
    public function updateTicket(string $format, int $tid):Response {
        $this->validatePermision(true); //will throw exception if invalid
        $params = $this->getParams($format);
        $user=$this->getUser($format, $params);
        $ticket = $this->getByTicketId($tid, $user);
        $vars=[
            'message'=>$params['message'],
            'userId'=>$user->getId(),
            'poster'=>$user->getFullName(),
            'ip_address'=>$_SERVER['REMOTE_ADDR'] //Use web client's IP
        ];
        /* The standard web interface provides the following to Ticket::postMessage()
        {
            "userId": 35,
            "poster": "Michael Reed",
            "message": "Response from web",
            "cannedattachments": [],
            "draft_id": ""
        }
        My API provides the following to Ticket::postMessage()
        {
            "message": "Another response.",
            "userId": 35,
            "poster": "Michael Reed",
            "ip_address": "74.208.80.161"
        }
        */
        $response = $ticket->postMessage($vars, 'api');//Ticket::postMessage($vars, $origin='', $alerts=true)
        return $this->response(200, $ticket);
    }
    public function getTickets(string $format):Response {
        //Future:  Allow for optional filtering for name and topic ID
        //syslog(LOG_INFO, "TicketApiController::getTickets() using $format");
        $this->validatePermision(); //will throw exception if invalid
        $filter=['user_id' => $this->getUser($format)->getId()];
        $params = $this->getParams($format);
        if(isset($params['statusId'])) {
            $filter['status_id']=$params['statusId'];
        }
        $tickets = Ticket::objects()->filter($filter)->all();
        return $this->response(200, $tickets?$tickets:[]);
    }
    public function getTopics(string $format):Response {
        //syslog(LOG_INFO, "TicketApiController::getTopics() using $format");
        //This API request does not need to provide user identifier.
        $this->validatePermision(); //will throw exception if invalid
        return $this->response(200, $this->createList(Topic::getPublicHelpTopics(), 'id', 'value'));
    }

    // Private methods to support new api methods.  Verify if existing osticket methods should be used instead.
    private function getByTicketId(int $ticketId, $user=null):Ticket {
        //Only pass $user if authentication is desired
        if(!$pk=Ticket::getIdByNumber($ticketId)){
            throw new ApiException('Unknown or invalid ticket ID.', 400);
        }
        return $this->getByPrimaryId($pk, $user);
    }
    private function getByPrimaryId(int $pk, $user=null):Ticket {
        //Only pass $user if authentication is desired
        if(!$ticket = Ticket::lookup($pk)) {
            throw new ApiException('Unknown or invalid ticket ID.', 400);
        }
        if($user && !$ticket->checkUserAccess($user)) {
            throw new ApiException('Unknown or invalid ticket ID.', 400); //Using generic message on purpose!
        }
        return $ticket;
    }
    private function createList(array $items, string $idName, string $valueName):array {
        $list=[];
        foreach($items as $key=>$value) {
            $list[]=[$idName=>$key, $valueName=>$value];
        }
        return $list;
    }
    private function getUser(string $format, array $params=[]):EndUser {
        //userId or email must be provided in request parameters
        $params=$params?$params:$this->getParams($format);
        syslog(LOG_INFO, json_encode($params));
        $user = TicketUser::lookupByEmail('theodog.test@gmail.com');
        if(isset($params['userId'])){
            if(!$user = TicketUser::lookupById($params['userId'])) {
                throw new ApiException('Invalid user.', 400);
            }
        }
        elseif(isset($params['email'])){
            if(!$user = TicketUser::lookupByEmail($params['email'])) {
                throw new ApiException('Invalid user.', 400);
            }
        }
        else {
            throw new ApiException('Either userId or email must be provided in request.', 400);
        }
        return $user;
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
            $this->response($result_code, $result);

        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('ticket'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response(500, $result);
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
        $this->response($result_code, $tickets);
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
            $this->response($result_code, $result);

        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('tickets'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response($result_code, $result);
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

            $this->response($result_code, $result);

        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('tickets'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response(500, $result);
        }
    }

    //staff replies to client ticket with the updated status
    public function postReply($format) {
        try{
            if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
                return $this->exerr(401, __('API key not authorized'));

            $data = $this->getParams($format);

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
            $this->response($result_code, $result);
        }
        catch ( Throwable $e){
            $msg = $e-> getMessage();
            $result =  array('tickets'=> array() ,'status_code' => 'FAILURE', 'status_msg' => $msg);
            $this->response(500, $result);
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
