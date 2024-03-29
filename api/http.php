<?php
/*********************************************************************
    http.php

    HTTP controller for the osTicket API

    Jared Hancock
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
// Use sessions — it's important for SSO authentication, which uses
// /api/auth/ext
define('DISABLE_SESSION', false);
require 'api.inc.php';

# Include the main api urls
require_once INCLUDE_DIR."class.dispatcher.php";

$dispatcher = patterns('',
        url_post("^/tickets\.(?P<format>xml|json|email)$", array('api.tickets.php:TicketApiController','create')),
        url('^/tasks/', patterns('',
                url_post("^cron$", array('api.cron.php:CronApiController', 'execute'))
        )),

        //I added the following endpoints.
        url_get("^/tickets\.(?P<format>xml|json)/(?P<tid>\d+)$", array('api.tickets.php:TicketApiController','getTicket')),  //Do first!
        url_get("^/tickets\.(?P<format>xml|json)", array('api.tickets.php:TicketApiController','getTickets')),
        url_post("^/tickets\.(?P<format>xml|json)/(?P<tid>\d+)$", array('api.tickets.php:TicketApiController','reopenTicket')),
        url_put("^/tickets\.(?P<format>xml|json)/(?P<tid>\d+)$", array('api.tickets.php:TicketApiController','updateTicket')),
        url_delete("^/tickets\.(?P<format>xml|json)/(?P<tid>\d+)$", array('api.tickets.php:TicketApiController','closeTicket')),
        url_get("^/topics\.(?P<format>xml|json)", array('api.tickets.php:TicketApiController','getTopics')),
        url_post("^/tickets/reply\.(?P<format>json)$", array('api.tickets.php:TicketApiController','postReply')),

        //User methods
        url_post("^/scp/users\.(?P<format>xml|json)$", array('api.users.php:UserApiController','create')),

        // The following were added by https://github.com/osTicket/osTicket/pull/4361/commits/781e15b0dd89c205d3999fb844e984b695a36368
        // I have not extensively tested them, and only changed the endpoint url by adding "scp" as well as the format extention
        url_get("^/scp/tickets\.(?P<format>xml|json)$", array('api.tickets.php:TicketApiController','restGetTickets')),
        url_get("^/scp/tickets/ticketInfo\.(?P<format>xml|json)$", array('api.tickets.php:TicketApiController','getTicketInfo')),
        url_get("^/scp/tickets/staffTickets\.(?P<format>xml|json)$", array('api.tickets.php:TicketApiController','getStaffTickets')),
        url_get("^/scp/tickets/clientTickets\.(?P<format>xml|json)$", array('api.tickets.php:TicketApiController','getClientTickets')),
        url_post("^/scp/tickets/reply\.(?P<format>xml|json)$", array('api.tickets.php:TicketApiController','postReply'))
);

Signal::send('api', $dispatcher);

# Call the respective function
try{
    $dispatcher->resolve($ost->get_path_info());;
}
catch (ApiException $e){
    Http::response($e->getCode(), json_encode(['error'=>__($e->getMessage())]));
}
