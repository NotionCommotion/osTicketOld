<?php

class HttpParserException extends \Exception{}
class HttpParser
{
    private $request;

    public function getRequest(bool $force=false) {
        if($this->request && !$force) return $this->request;
        switch(strtoupper($_SERVER['REQUEST_METHOD'])){
            //Question.  Is using PHP super globals $_GET and $_POST better/faster/etc for GET and POST requests than returning stream?
            case 'GET': $request=$_GET;break;
            case 'POST': $request=$_POST;break;
            case 'PUT': $request=$this->getStream();break;
            case 'DELETE': $request=$this->getStream();break;
            default: throw new HttpParserException("Unsupported HTTP method $_SERVER[REQUEST_METHOD]'", 500);
        }
        $this->request=$request;
        return $request;
    }

    public function response($data, int $code=null) {
        if($code) http_response_code($code);
        $type=$this->getBestSupportedMimeType(["application/json", "application/xml"]);
        header('Content-Type: '.$type);
        switch($type){
            case "application/xml":
                $xml_data = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
                $this->array_to_xml($data,$xml_data);
                echo($xml_data->asXML());
                break;
            default:    //case 'application/json':;
                echo json_encode($data);
        }
        exit;
    }

    private function getStream() {
        $input = substr(PHP_SAPI, 0, 3) === 'cgi'?'php://stdin':'php://input';

        //Question.  Why not just use: parse_str(file_get_contents($input), $contents);

        if (!($stream = @fopen($input, 'r'))) {
            throw new HttpParserException('Unable to read request body', 500);
        }
        if (!is_resource($stream)) {
            throw new HttpParserException('Invalid stream', 500);
        }
        $str = '';
        while (!feof($stream)) {
            $str .= fread($stream, 8192);
        }
        parse_str($str, $contents);

        return $contents;
    }

    private function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    private function getBestSupportedMimeType($mimeTypes = null) {
        // Values will be stored in this array
        $AcceptTypes = [];
        $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
        $accept = explode(',', $accept);
        foreach ($accept as $a) {
             $q = 1;  // the default quality is 1.
            // check if there is a different quality
            if (strpos($a, ';q=')) {
                // divide "mime/type;q=X" into two parts: "mime/type" i "X"
                list($a, $q) = explode(';q=', $a);
            }
            // mime-type $a is accepted with the quality $q
            // WARNING: $q == 0 means, that mime-type isn’t supported!
            $AcceptTypes[$a] = $q;
        }
        arsort($AcceptTypes);

        // if no parameter was passed, just return parsed data
        if (!$mimeTypes) return $AcceptTypes;

        //If supported mime-type exists, return it, else return null
        $mimeTypes = array_map('strtolower', (array)$mimeTypes);
        foreach ($AcceptTypes as $mime => $q) {
            if ($q && in_array($mime, $mimeTypes)) return $mime;
        }
        return null;
    }
}
