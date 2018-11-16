<?php

include_once INCLUDE_DIR.'class.api.php';
include_once INCLUDE_DIR.'class.user.php';

class ApiException extends \Exception{} //There is probably an existing OSTicket Exception which should be used

class UserApiController extends ApiController {

    public function create(string $format):Response {
        //see ajax.users.php addUser() and class.api.php for example
        //syslog(LOG_INFO, "UserApiController::create() using $format");
        $api=$this->getApi(true); //Should this be used.  Currently only fetched to validate API key.
        $params = $this->getParams($format);
        //Maybe use osTicket validation methods instead?
        $params=array_intersect_key($params, array_flip(['phone','notes','name','email','timezone','password']));
        if (count($params)!==6) {
            $missing=array_diff(['phone','notes','name','email','timezone','password'], array_keys($params));
            throw new ApiException('Missing parameters '.implode(', ', $missing), 400);
        }
        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ApiException("email $params[email] is already in use.", 400);
        }
        if(User::lookup(['emails__address'=>$params['email']])) {
            throw new ApiException("email $params[email] is already in use.", 400);
        }
        if(!$user=User::fromVars($params)) {
            throw new ApiException('Unknown user creation error', 400);
        }
        $errors=[];
        $params=array_merge($params,['username'=>$params['email'],'passwd1'=>$params['password'],'passwd2'=>$params['password'],'timezone'=>$params['timezone']]);
        if(!$user->register($params, $errors)) {
            throw new ApiException('User added but error attempting to register', 400);
        }
        return $this->response(201, $user->to_json());
    }

    //Move these and ticket common methods to parent
    private function getParams(string $format):array {
        return $_SERVER['REQUEST_METHOD']==='GET'?$_GET:$this->getRequest($format);
    }
    private function getApi($create=false):API {
        if(!($api=$this->requireApiKey()) || $create && !$api->canCreateTickets()) {
            throw new ApiException('API key not authorized.', 401);
        }
        return $api;
    }
}