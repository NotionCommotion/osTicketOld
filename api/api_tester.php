<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <title>OS Ticket API tester</title>
    </head>
    <body>
        <div>
            <p>view console.log</p>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script type="text/javascript">
            $(function() {

                function testApi(stack) {
                    if(stack.length===0) return;
                    var o=stack.shift();
                    $.ajax({
                        type: o.method,
                        url: o.url,
                        headers: {"X-API-Key": "D605900B7C1AC09BB600700F31D8E339"},
                        //osticket's existing api create method requires all requests in body (post, put, delete) to be a string, so do the same to be consistant
                        data: o.method=='GET'?o.data:JSON.stringify(o.data),
                        dataType: 'json',
                        success: function (rsp){
                            console.log('success', o.test, o.method, o.url, o.data, rsp);
                            testApi(stack)
                        },
                        error: function (xhr) {
                            console.log('error', o.test, o.method, o.url, o.data, xhr);
                            testApi(stack)
                        }
                    });
                }
                var email='theodog.test@gmail.com';
                var ticketId=839034;

                var postData={
                    "alert": true,
                    "autorespond": true,
                    "source": "API",
                    "name": "Angry User",
                    "email": "api@osticket.com",
                    "phone": "3185558634X123",
                    "subject": "Testing API",
                    "ip": "123.211.233.122",
                    "message": "data:text/html,MESSAGE <b>HERE</b>",
                    "attachments": [
                        {"file.txt": "data:text/plain;charset=utf-8,content"},
                        {"image.png": "data:image/png;base64,R0lGODdhMAA..."},
                    ]
                };

                postData={
                    email: email,
                    //"message": "data:text/html, My original message",
                    "message": "My original message",
                    "name": "John Doe",
                    "subject": "Testing API",
                };

                var stack=[
                    //{test: 'getTickets', method: 'GET', url: '/api/tickets.json', data: {email: email}},
                    //{test: 'getTopics', method: 'GET', url: '/api/topics.json', data: {}},
                    //{test: 'getTicket', method: 'GET', url: '/api/tickets.json/'+ticketId, data: {email: email}},
                    {test: 'closeTicket', method: 'DELETE', url: '/api/tickets.json/'+ticketId, data: {email: email}},
                    //{test: 'reopenTicket', method: 'POST', url: '/api/tickets.json/'+ticketId, data: {email: email}},
                    //{test: 'updateTicket', method: 'PUT', url: '/api/tickets.json/'+ticketId, data: {email: email, "message": "My updated message"}},
                    //{test: 'create', method: 'POST', url: '/api/tickets.json', data: postData},
                ];
                testApi(stack);
            });
        </script>
    </body>
</html>