<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>OS Ticket API tester</title>
    </head>
    <body>
        <div>
            <div id='output'></div>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script>
            $(function() {

                var output=$('#output');
                function logResults(status, test, method, url, data, rsp){
                    //console.log(status, test, method, url, data, rsp)
                    output.append('<h4>'+test+'</h4><p>'+method+' '+url+'</p><p>params:</p><pre><code>'+JSON.stringify(data, null, 2)+'</code></pre><p>Status: '+status+'</p><p>Response:</p><pre><code>'+JSON.stringify(rsp, null, 2)+'</code></pre><br>');
                }

                function testApi(stack, api) {
                    if(stack.length===0) return;
                    var o=stack.shift();
                    $.ajax({
                        type: o.method,
                        url: o.url,
                        headers: {"X-API-Key": api},
                        //osticket's existing api create method requires all requests in body (post, put, delete) to be a string, so do the same to be consistant
                        data: o.method=='GET'?o.data:JSON.stringify(o.data),
                        dataType: 'json',
                        success: function (rsp){
                            logResults('success', o.test, o.method, o.url, o.data, rsp);
                            testApi(stack, api)
                        },
                        error: function (xhr) {
                            logResults('error', o.test, o.method, o.url, o.data, xhr);
                            testApi(stack, api)
                        }
                    });
                }
                var email='theodog.test@gmail.com',
                user_id=5,
                staffUserName='Michael',
                ticketId=508809,
                api='D605900B7C1AC09BB600700F31D8E339';

                var postData1={
                    "email": "api@osticket.com",
                    "name": "Angry User",
                    "subject": "Testing API",
                    "message": "data:text/html,MESSAGE <b>HERE</b>",
                    "alert": true,
                    "autorespond": true,
                    "ip": "123.211.233.122",
                    "priority": 123,    //priority id
                    "source": "API",
                    "topicId": 123,
                    "phone": "3185558634X123",
                    "attachments": [
                        {"file.txt": "data:text/plain;charset=utf-8,content"},
                        {"image.png": "data:image/png;base64,R0lGODdhMAA..."},
                    ]
                };

                var postData2={
                    email: email,
                    //"message": "data:text/html, My original message",
                    "message": "My original message",
                    "name": "John Doe",
                    "subject": "Testing API",
                    "topicId": 2,
                };

                var postData3={
                    "ticketNumber" : ticketId,
                    "msgId" : "",
                    "a" : "reply",
                    "emailreply" : "1",
                    "emailcollab" : "1",
                    "cannedResp" : "0",
                    "draft_id" : "",
                    "response" : "ticket issue is resolved !",
                    "signature" : "none",
                    "reply_status_id" : "1",
                    "staffUserName" : staffUserName,
                    "ip_address" : "::1",
                    "cannedattachments" : ""
                };

                var stack=[
                    //my endpoints (accessed by end user)
                    {test: 'getTickets', method: 'GET', url: '/api/tickets.json', data: {user_id: user_id}},
                    {test: 'getTickets', method: 'GET', url: '/api/tickets.json', data: {email: email}},
                    {test: 'getTopics', method: 'GET', url: '/api/topics.json', data: {}},
                    {test: 'getTicket', method: 'GET', url: '/api/tickets.json/'+ticketId, data: {}},
                    {test: 'closeTicket', method: 'DELETE', url: '/api/tickets.json/'+ticketId, data: {email: email}},
                    {test: 'reopenTicket', method: 'POST', url: '/api/tickets.json/'+ticketId, data: {email: email}},
                    {test: 'updateTicket', method: 'PUT', url: '/api/tickets.json/'+ticketId, data: {email: email, "message": "My updated message"}},
                    {test: 'create', method: 'POST', url: '/api/tickets.json', data: postData2},

                    //amalmagdy's endpoints (accessed by staff user)
                    {test: 'get ticket info', method: 'GET', url: '/api/scp/tickets/ticketInfo.json', data: {ticketNumber: ticketId}},
                    {test: 'get staff tickets', method: 'GET', url: '/api/scp/tickets/staffTickets.json', data: {staffUserName: staffUserName}},
                    {test: 'get client tickets', method: 'GET', url: '/api/scp/tickets/clientTickets.json', data: {clientUserMail: email}},
                    {test: 'post reply to ticket with ticket new status', method: 'POST', url: '/api/scp/tickets/reply.json', data: postData3},
                    // {test: 'restGetTickets', method: 'GET', url: '/api/scp/tickets.json', data: {}}, //What is the point of this endpoint?
                ];
                testApi(stack, api);
            });
        </script>
    </body>
</html>
