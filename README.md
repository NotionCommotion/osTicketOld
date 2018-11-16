# Additional osTicket API endpoints.

This script extends https://github.com/osTicket.

The intent of these changes are to allow a public help desk to be embedded in a general purpose website, and for admin and staff to utilize the standard backend osTicket interface.

osTicket currently only allows the creation of a new ticket described by https://docs.osticket.com/en/latest/Developer%20Documentation/API/Tickets.html, and this repository extends osTickets with the following functionality:

1. Display a given users tickets.
2. Display available topics.
3. Display a ticket based on a given ticket ID.
4. Close a ticket.
5. Reopen a ticket.
6. Post a reply to an existing ticket.
7. Create new topic (minor modifications to core method).

In addition to GET, POST, and DELETE HTTP methods, PUT was added.

In addition, amalmagdy's code (https://github.com/osTicket/osTicket/pull/4361) whose purpose appears to be targeted for backend user needs was slightly modified and added.  Functionality includes the following:

1. Retrieve ticket details.
2. Get list of tickets issued by one user.
3. Get list of tickets assigned to an agent (staff member).
4. Post a reply message to one ticket with updated status. i.e. change ticket status from open to closed.

## Tested On:
PHP Version 7.1.24, Apache/2.4.6, CentOS Linux release 7.5.1804

## How repository was created:
Since osTickets is not fully compatible with PHP7, https://github.com/osTicket/osTicket/releases/download/v1.10.4/osTicket-v1.10.4.zip was installed.  I probably could have just cloned the corresponding branch from github, but couldn't find it.  To make it work with my version of PHP, class.ostsession.php was modified as shown:

```
class DbSessionBackend extends SessionBackend {
    function read($id) {
        ...
        if(!is_string($this->data->session_data)) {
            $this->data->session_data = strval($this->data->session_data);
        }
        return $this->data->session_data;
    }
}
```


## TODO:
1. Confirm that posting a reply to an existing ticket with attachments work.
2. Figure out how to include attachments when retrieving ticket.
4. Clean up threads.
5. Changes to allow specific user name to be logged as making changes instead of "SYSTEM".
6. Better utilize exising osTicket methods where applicable.
7. Figure out how to make this a pull request from the osTicket master.

## Demo

/api/api_tester.php will execute each method the response can be viewed using your browser inspector.  Change the API key, email, and user name to reflect your installation.


## Usage

Perform curl requests as shown.
All ticket methods which change the database require either the user's email (email) or id (user_id).  Only adding a new ticket uses this information to directly insert into the database and the other's use it just to log who made the change.

```
getTickets
GET /api/tickets.json

params:

{
  "user_id": 5
}
Status: success

Response:

[
  {
    "ticket_number": "112356",
    "subject": "Testing API",
    "ticket_status": "Open",
    "statusId": 1,
    "priority": "Low",
    "department": "Support",
    "create_timestamp": "2018-11-16 13:21:53",
    "user": {
      "fullname": "Michael User2",
      "firstname": "Michael",
      "lastname": "User2",
      "email": "theodog.test@gmail.com",
      "phone": ""
    },
    "source": "API",
    "due_timestamp": "2018-11-18 13:21:53",
    "close_timestamp": null,
    "topic": "Feedback",
    "topicId": 2,
    "last_message_timestamp": "2018-11-16 13:21:53",
    "last_response_timestamp": null,
    "assigned_to": [],
    "threads": [
      {
        "id": 278,
        "pid": 0,
        "thread_id": 104,
        "staff_id": null,
        "user_id": 5,
        "type": "M",
        "poster": "Michael User2",
        "editor": null,
        "source": "API",
        "title": "Testing API",
        "body": "My original message",
        "message": {
          "body": "My original message",
          "type": "text",
          "stripped_images": [],
          "embedded_images": [],
          "options": {
            "strip-embedded": true
          }
        },
        "format": "text",
        "created": "2018-11-16 13:21:53",
        "updated": "0000-00-00 00:00:00",
        "staff_name": null,
        "user_name": {
          "format": "original",
          "parts": {
            "salutation": "",
            "first": "Michael",
            "suffix": "",
            "last": "User2",
            "middle": ""
          },
          "name": "Michael User2"
        }
      }
    ]
  }
]

getTickets
GET /api/tickets.json

params:

{
  "email": "theodog.test@gmail.com"
}
Status: success

Response:

[
  {
    "ticket_number": "112356",
    "subject": "Testing API",
    "ticket_status": "Open",
    "statusId": 1,
    "priority": "Low",
    "department": "Support",
    "create_timestamp": "2018-11-16 13:21:53",
    "user": {
      "fullname": "Michael User2",
      "firstname": "Michael",
      "lastname": "User2",
      "email": "theodog.test@gmail.com",
      "phone": ""
    },
    "source": "API",
    "due_timestamp": "2018-11-18 13:21:53",
    "close_timestamp": null,
    "topic": "Feedback",
    "topicId": 2,
    "last_message_timestamp": "2018-11-16 13:21:53",
    "last_response_timestamp": null,
    "assigned_to": [],
    "threads": [
      {
        "id": 278,
        "pid": 0,
        "thread_id": 104,
        "staff_id": null,
        "user_id": 5,
        "type": "M",
        "poster": "Michael User2",
        "editor": null,
        "source": "API",
        "title": "Testing API",
        "body": "My original message",
        "message": {
          "body": "My original message",
          "type": "text",
          "stripped_images": [],
          "embedded_images": [],
          "options": {
            "strip-embedded": true
          }
        },
        "format": "text",
        "created": "2018-11-16 13:21:53",
        "updated": "0000-00-00 00:00:00",
        "staff_name": null,
        "user_name": {
          "format": "original",
          "parts": {
            "salutation": "",
            "first": "Michael",
            "suffix": "",
            "last": "User2",
            "middle": ""
          },
          "name": "Michael User2"
        }
      }
    ]
  }
]

getTopics
GET /api/topics.json

params:

{}
Status: success

Response:

[
  {
    "id": 2,
    "value": "Feedback"
  },
  {
    "id": 1,
    "value": "General Inquiry"
  },
  {
    "id": 10,
    "value": "Report a Problem"
  },
  {
    "id": 11,
    "value": "Report a Problem / Access Issue"
  }
]

getTicket
GET /api/tickets.json/112356

params:

{}
Status: success

Response:

{
  "ticket_number": "112356",
  "subject": "Testing API",
  "ticket_status": "Open",
  "statusId": 1,
  "priority": "Low",
  "department": "Support",
  "create_timestamp": "2018-11-16 13:21:53",
  "user": {
    "fullname": "Michael User2",
    "firstname": "Michael",
    "lastname": "User2",
    "email": "theodog.test@gmail.com",
    "phone": ""
  },
  "source": "API",
  "due_timestamp": "2018-11-18 13:21:53",
  "close_timestamp": null,
  "topic": "Feedback",
  "topicId": 2,
  "last_message_timestamp": "2018-11-16 13:21:53",
  "last_response_timestamp": null,
  "assigned_to": [],
  "threads": [
    {
      "id": 278,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": 5,
      "type": "M",
      "poster": "Michael User2",
      "editor": null,
      "source": "API",
      "title": "Testing API",
      "body": "My original message",
      "message": {
        "body": "My original message",
        "type": "text",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": true
        }
      },
      "format": "text",
      "created": "2018-11-16 13:21:53",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": {
        "format": "original",
        "parts": {
          "salutation": "",
          "first": "Michael",
          "suffix": "",
          "last": "User2",
          "middle": ""
        },
        "name": "Michael User2"
      }
    }
  ]
}

closeTicket
DELETE /api/tickets.json/112356

params:

{
  "user_id": 5
}
Status: success

Response:

undefined

reopenTicket
POST /api/tickets.json/112356

params:

{
  "user_id": 5
}
Status: success

Response:

{
  "ticket_number": "112356",
  "subject": "Testing API",
  "ticket_status": "Open",
  "statusId": 1,
  "priority": "Low",
  "department": "Support",
  "create_timestamp": "2018-11-16 13:21:53",
  "user": {
    "fullname": "Michael User2",
    "firstname": "Michael",
    "lastname": "User2",
    "email": "theodog.test@gmail.com",
    "phone": ""
  },
  "source": "API",
  "due_timestamp": "2018-11-18 13:21:53",
  "close_timestamp": "2018-11-16 13:23:21",
  "topic": "Feedback",
  "topicId": 2,
  "last_message_timestamp": "2018-11-16 13:21:53",
  "last_response_timestamp": null,
  "assigned_to": [],
  "threads": [
    {
      "id": 278,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": 5,
      "type": "M",
      "poster": "Michael User2",
      "editor": null,
      "source": "API",
      "title": "Testing API",
      "body": "My original message",
      "message": {
        "body": "My original message",
        "type": "text",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": true
        }
      },
      "format": "text",
      "created": "2018-11-16 13:21:53",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": {
        "format": "original",
        "parts": {
          "salutation": "",
          "first": "Michael",
          "suffix": "",
          "last": "User2",
          "middle": ""
        },
        "name": "Michael User2"
      }
    },
    {
      "id": 280,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "N",
      "poster": "SYSTEM",
      "editor": null,
      "source": "",
      "title": "Status Changed",
      "body": "Closed by user",
      "message": {
        "body": "Closed by user",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:20",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    }
  ]
}

closeTicket
DELETE /api/tickets.json/112356

params:

{
  "email": "theodog.test@gmail.com"
}
Status: success

Response:

undefined

reopenTicket
POST /api/tickets.json/112356

params:

{
  "email": "theodog.test@gmail.com"
}
Status: success

Response:

{
  "ticket_number": "112356",
  "subject": "Testing API",
  "ticket_status": "Open",
  "statusId": 1,
  "priority": "Low",
  "department": "Support",
  "create_timestamp": "2018-11-16 13:21:53",
  "user": {
    "fullname": "Michael User2",
    "firstname": "Michael",
    "lastname": "User2",
    "email": "theodog.test@gmail.com",
    "phone": ""
  },
  "source": "API",
  "due_timestamp": "2018-11-18 13:21:53",
  "close_timestamp": "2018-11-16 13:23:22",
  "topic": "Feedback",
  "topicId": 2,
  "last_message_timestamp": "2018-11-16 13:21:53",
  "last_response_timestamp": null,
  "assigned_to": [],
  "threads": [
    {
      "id": 278,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": 5,
      "type": "M",
      "poster": "Michael User2",
      "editor": null,
      "source": "API",
      "title": "Testing API",
      "body": "My original message",
      "message": {
        "body": "My original message",
        "type": "text",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": true
        }
      },
      "format": "text",
      "created": "2018-11-16 13:21:53",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": {
        "format": "original",
        "parts": {
          "salutation": "",
          "first": "Michael",
          "suffix": "",
          "last": "User2",
          "middle": ""
        },
        "name": "Michael User2"
      }
    },
    {
      "id": 280,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "N",
      "poster": "SYSTEM",
      "editor": null,
      "source": "",
      "title": "Status Changed",
      "body": "Closed by user",
      "message": {
        "body": "Closed by user",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:20",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    },
    {
      "id": 281,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "N",
      "poster": "SYSTEM",
      "editor": null,
      "source": "",
      "title": "Status Changed",
      "body": "Closed by user",
      "message": {
        "body": "Closed by user",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:21",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    }
  ]
}

updateTicket
PUT /api/tickets.json/112356

params:

{
  "user_id": 5,
  "message": "My updated message using user_id"
}
Status: success

Response:

{
  "ticket_number": "112356",
  "subject": "Testing API",
  "ticket_status": "Open",
  "statusId": 1,
  "priority": "Low",
  "department": "Support",
  "create_timestamp": "2018-11-16 13:21:53",
  "user": {
    "fullname": "Michael User2",
    "firstname": "Michael",
    "lastname": "User2",
    "email": "theodog.test@gmail.com",
    "phone": ""
  },
  "source": "API",
  "due_timestamp": "2018-11-18 13:21:53",
  "close_timestamp": "2018-11-16 13:23:22",
  "topic": "Feedback",
  "topicId": 2,
  "last_message_timestamp": "2018-11-16 13:23:22",
  "last_response_timestamp": null,
  "assigned_to": [],
  "threads": [
    {
      "id": 278,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": 5,
      "type": "M",
      "poster": "Michael User2",
      "editor": null,
      "source": "API",
      "title": "Testing API",
      "body": "My original message",
      "message": {
        "body": "My original message",
        "type": "text",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": true
        }
      },
      "format": "text",
      "created": "2018-11-16 13:21:53",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": {
        "format": "original",
        "parts": {
          "salutation": "",
          "first": "Michael",
          "suffix": "",
          "last": "User2",
          "middle": ""
        },
        "name": "Michael User2"
      }
    },
    {
      "id": 280,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "N",
      "poster": "SYSTEM",
      "editor": null,
      "source": "",
      "title": "Status Changed",
      "body": "Closed by user",
      "message": {
        "body": "Closed by user",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:20",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    },
    {
      "id": 281,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "N",
      "poster": "SYSTEM",
      "editor": null,
      "source": "",
      "title": "Status Changed",
      "body": "Closed by user",
      "message": {
        "body": "Closed by user",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:21",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    },
    {
      "id": 282,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "M",
      "poster": "",
      "editor": null,
      "source": "",
      "title": null,
      "body": "My updated message using user_id",
      "message": {
        "body": "My updated message using user_id",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:22",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    }
  ]
}

updateTicket
PUT /api/tickets.json/112356

params:

{
  "email": "theodog.test@gmail.com",
  "message": "My updated message using email"
}
Status: success

Response:

{
  "ticket_number": "112356",
  "subject": "Testing API",
  "ticket_status": "Open",
  "statusId": 1,
  "priority": "Low",
  "department": "Support",
  "create_timestamp": "2018-11-16 13:21:53",
  "user": {
    "fullname": "Michael User2",
    "firstname": "Michael",
    "lastname": "User2",
    "email": "theodog.test@gmail.com",
    "phone": ""
  },
  "source": "API",
  "due_timestamp": "2018-11-18 13:21:53",
  "close_timestamp": "2018-11-16 13:23:22",
  "topic": "Feedback",
  "topicId": 2,
  "last_message_timestamp": {
    "alias": null,
    "func": "NOW",
    "args": []
  },
  "last_response_timestamp": null,
  "assigned_to": [],
  "threads": [
    {
      "id": 278,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": 5,
      "type": "M",
      "poster": "Michael User2",
      "editor": null,
      "source": "API",
      "title": "Testing API",
      "body": "My original message",
      "message": {
        "body": "My original message",
        "type": "text",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": true
        }
      },
      "format": "text",
      "created": "2018-11-16 13:21:53",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": {
        "format": "original",
        "parts": {
          "salutation": "",
          "first": "Michael",
          "suffix": "",
          "last": "User2",
          "middle": ""
        },
        "name": "Michael User2"
      }
    },
    {
      "id": 280,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "N",
      "poster": "SYSTEM",
      "editor": null,
      "source": "",
      "title": "Status Changed",
      "body": "Closed by user",
      "message": {
        "body": "Closed by user",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:20",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    },
    {
      "id": 281,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "N",
      "poster": "SYSTEM",
      "editor": null,
      "source": "",
      "title": "Status Changed",
      "body": "Closed by user",
      "message": {
        "body": "Closed by user",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:21",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    },
    {
      "id": 282,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "M",
      "poster": "",
      "editor": null,
      "source": "",
      "title": null,
      "body": "My updated message using user_id",
      "message": {
        "body": "My updated message using user_id",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:22",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    },
    {
      "id": 283,
      "pid": 0,
      "thread_id": 104,
      "staff_id": null,
      "user_id": null,
      "type": "M",
      "poster": "",
      "editor": null,
      "source": "",
      "title": null,
      "body": "My updated message using email",
      "message": {
        "body": "My updated message using email",
        "type": "html",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": false,
          "balanced": true
        }
      },
      "format": "html",
      "created": "2018-11-16 13:23:22",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": null
    }
  ]
}

create
POST /api/tickets.json

params:

{
  "email": "theodog.test@gmail.com",
  "message": "My original message",
  "name": "John Doe",
  "subject": "Testing API",
  "topicId": 2
}
Status: success

Response:

{
  "ticket_number": "458068",
  "subject": "Testing API",
  "ticket_status": "Open",
  "statusId": 1,
  "priority": "Low",
  "department": "Support",
  "create_timestamp": "2018-11-16 13:23:23",
  "user": {
    "fullname": "Michael User2",
    "firstname": "Michael",
    "lastname": "User2",
    "email": "theodog.test@gmail.com",
    "phone": ""
  },
  "source": "API",
  "due_timestamp": "2018-11-18 13:23:23",
  "close_timestamp": null,
  "topic": "Feedback",
  "topicId": 2,
  "last_message_timestamp": "2018-11-16 13:23:23",
  "last_response_timestamp": null,
  "assigned_to": [],
  "threads": [
    {
      "id": 284,
      "pid": 0,
      "thread_id": 105,
      "staff_id": null,
      "user_id": 5,
      "type": "M",
      "poster": "Michael User2",
      "editor": null,
      "source": "API",
      "title": "Testing API",
      "body": "My original message",
      "message": {
        "body": "My original message",
        "type": "text",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": true
        }
      },
      "format": "text",
      "created": "2018-11-16 13:23:23",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": {
        "format": "original",
        "parts": {
          "salutation": "",
          "first": "Michael",
          "suffix": "",
          "last": "User2",
          "middle": ""
        },
        "name": "Michael User2"
      }
    }
  ]
}

create
POST /api/tickets.json

params:

{
  "user_id": 5,
  "message": "My original message",
  "name": "John Doe",
  "subject": "Testing API",
  "topicId": 2
}
Status: success

Response:

{
  "ticket_number": "888010",
  "subject": "Testing API",
  "ticket_status": "Open",
  "statusId": 1,
  "priority": "Low",
  "department": "Support",
  "create_timestamp": "2018-11-16 13:23:23",
  "user": {
    "fullname": "Michael User2",
    "firstname": "Michael",
    "lastname": "User2",
    "email": "theodog.test@gmail.com",
    "phone": ""
  },
  "source": "API",
  "due_timestamp": "2018-11-18 13:23:23",
  "close_timestamp": null,
  "topic": "Feedback",
  "topicId": 2,
  "last_message_timestamp": "2018-11-16 13:23:23",
  "last_response_timestamp": null,
  "assigned_to": [],
  "threads": [
    {
      "id": 285,
      "pid": 0,
      "thread_id": 106,
      "staff_id": null,
      "user_id": 5,
      "type": "M",
      "poster": "Michael User2",
      "editor": null,
      "source": "API",
      "title": "Testing API",
      "body": "My original message",
      "message": {
        "body": "My original message",
        "type": "text",
        "stripped_images": [],
        "embedded_images": [],
        "options": {
          "strip-embedded": true
        }
      },
      "format": "text",
      "created": "2018-11-16 13:23:23",
      "updated": "0000-00-00 00:00:00",
      "staff_name": null,
      "user_name": {
        "format": "original",
        "parts": {
          "salutation": "",
          "first": "Michael",
          "suffix": "",
          "last": "User2",
          "middle": ""
        },
        "name": "Michael User2"
      }
    }
  ]
}

create
POST /api/scp/users.json

params:

{
  "phone": "4254441212X123",
  "notes": "Mynotes",
  "name": "john doe",
  "email": "new.user@gmail.com",
  "password": "thepassword",
  "timezone": "America/Los_Angeles"
}
Status: success

Response:

{
  "id": 19,
  "name": "john doe",
  "email": "new.user@gmail.com",
  "phone": "(425) 444-1212 x123"
}

get ticket info
GET /api/scp/tickets/ticketInfo.json

params:

{
  "ticketNumber": 112356
}
Status: success

Response:

{
  "ticket": {
    "ticket_number": "112356",
    "subject": "Testing API",
    "ticket_status": "Open",
    "statusId": 1,
    "priority": "Low",
    "department": "Support",
    "create_timestamp": "2018-11-16 13:21:53",
    "user": {
      "fullname": "Michael User2",
      "firstname": "Michael",
      "lastname": "User2",
      "email": "theodog.test@gmail.com",
      "phone": ""
    },
    "source": "API",
    "due_timestamp": "2018-11-18 13:21:53",
    "close_timestamp": "2018-11-16 13:23:22",
    "topic": "Feedback",
    "topicId": 2,
    "last_message_timestamp": "2018-11-16 13:23:22",
    "last_response_timestamp": null,
    "assigned_to": [],
    "threads": [
      {
        "id": 278,
        "pid": 0,
        "thread_id": 104,
        "staff_id": null,
        "user_id": 5,
        "type": "M",
        "poster": "Michael User2",
        "editor": null,
        "source": "API",
        "title": "Testing API",
        "body": "My original message",
        "message": {
          "body": "My original message",
          "type": "text",
          "stripped_images": [],
          "embedded_images": [],
          "options": {
            "strip-embedded": true
          }
        },
        "format": "text",
        "created": "2018-11-16 13:21:53",
        "updated": "0000-00-00 00:00:00",
        "staff_name": null,
        "user_name": {
          "format": "original",
          "parts": {
            "salutation": "",
            "first": "Michael",
            "suffix": "",
            "last": "User2",
            "middle": ""
          },
          "name": "Michael User2"
        }
      },
      {
        "id": 280,
        "pid": 0,
        "thread_id": 104,
        "staff_id": null,
        "user_id": null,
        "type": "N",
        "poster": "SYSTEM",
        "editor": null,
        "source": "",
        "title": "Status Changed",
        "body": "Closed by user",
        "message": {
          "body": "Closed by user",
          "type": "html",
          "stripped_images": [],
          "embedded_images": [],
          "options": {
            "strip-embedded": false,
            "balanced": true
          }
        },
        "format": "html",
        "created": "2018-11-16 13:23:20",
        "updated": "0000-00-00 00:00:00",
        "staff_name": null,
        "user_name": null
      },
      {
        "id": 281,
        "pid": 0,
        "thread_id": 104,
        "staff_id": null,
        "user_id": null,
        "type": "N",
        "poster": "SYSTEM",
        "editor": null,
        "source": "",
        "title": "Status Changed",
        "body": "Closed by user",
        "message": {
          "body": "Closed by user",
          "type": "html",
          "stripped_images": [],
          "embedded_images": [],
          "options": {
            "strip-embedded": false,
            "balanced": true
          }
        },
        "format": "html",
        "created": "2018-11-16 13:23:21",
        "updated": "0000-00-00 00:00:00",
        "staff_name": null,
        "user_name": null
      },
      {
        "id": 282,
        "pid": 0,
        "thread_id": 104,
        "staff_id": null,
        "user_id": null,
        "type": "M",
        "poster": "",
        "editor": null,
        "source": "",
        "title": null,
        "body": "My updated message using user_id",
        "message": {
          "body": "My updated message using user_id",
          "type": "html",
          "stripped_images": [],
          "embedded_images": [],
          "options": {
            "strip-embedded": false,
            "balanced": true
          }
        },
        "format": "html",
        "created": "2018-11-16 13:23:22",
        "updated": "0000-00-00 00:00:00",
        "staff_name": null,
        "user_name": null
      },
      {
        "id": 283,
        "pid": 0,
        "thread_id": 104,
        "staff_id": null,
        "user_id": null,
        "type": "M",
        "poster": "",
        "editor": null,
        "source": "",
        "title": null,
        "body": "My updated message using email",
        "message": {
          "body": "My updated message using email",
          "type": "html",
          "stripped_images": [],
          "embedded_images": [],
          "options": {
            "strip-embedded": false,
            "balanced": true
          }
        },
        "format": "html",
        "created": "2018-11-16 13:23:22",
        "updated": "0000-00-00 00:00:00",
        "staff_name": null,
        "user_name": null
      }
    ]
  },
  "status_code": "0",
  "status_msg": "ticket details retrieved successfully"
}

get staff tickets
GET /api/scp/tickets/staffTickets.json

params:

{
  "staffUserName": "Michael"
}
Status: success

Response:

{
  "tickets": [],
  "status_code": "0",
  "status_msg": "success"
}

get client tickets
GET /api/scp/tickets/clientTickets.json

params:

{
  "clientUserMail": "theodog.test@gmail.com"
}
Status: success

Response:

{
  "tickets": [
    {
      "ticket_number": "112356",
      "subject": "Testing API",
      "ticket_status": "Open",
      "statusId": 1,
      "priority": "Low",
      "department": "Support",
      "create_timestamp": "2018-11-16 13:21:53",
      "user": {
        "fullname": "Michael User2",
        "firstname": "Michael",
        "lastname": "User2",
        "email": "theodog.test@gmail.com",
        "phone": ""
      },
      "source": "API",
      "due_timestamp": "2018-11-18 13:21:53",
      "close_timestamp": "2018-11-16 13:23:22",
      "topic": "Feedback",
      "topicId": 2,
      "last_message_timestamp": "2018-11-16 13:23:22",
      "last_response_timestamp": null,
      "assigned_to": [],
      "threads": [
        {
          "id": 278,
          "pid": 0,
          "thread_id": 104,
          "staff_id": null,
          "user_id": 5,
          "type": "M",
          "poster": "Michael User2",
          "editor": null,
          "source": "API",
          "title": "Testing API",
          "body": "My original message",
          "message": {
            "body": "My original message",
            "type": "text",
            "stripped_images": [],
            "embedded_images": [],
            "options": {
              "strip-embedded": true
            }
          },
          "format": "text",
          "created": "2018-11-16 13:21:53",
          "updated": "0000-00-00 00:00:00",
          "staff_name": null,
          "user_name": {
            "format": "original",
            "parts": {
              "salutation": "",
              "first": "Michael",
              "suffix": "",
              "last": "User2",
              "middle": ""
            },
            "name": "Michael User2"
          }
        },
        {
          "id": 280,
          "pid": 0,
          "thread_id": 104,
          "staff_id": null,
          "user_id": null,
          "type": "N",
          "poster": "SYSTEM",
          "editor": null,
          "source": "",
          "title": "Status Changed",
          "body": "Closed by user",
          "message": {
            "body": "Closed by user",
            "type": "html",
            "stripped_images": [],
            "embedded_images": [],
            "options": {
              "strip-embedded": false,
              "balanced": true
            }
          },
          "format": "html",
          "created": "2018-11-16 13:23:20",
          "updated": "0000-00-00 00:00:00",
          "staff_name": null,
          "user_name": null
        },
        {
          "id": 281,
          "pid": 0,
          "thread_id": 104,
          "staff_id": null,
          "user_id": null,
          "type": "N",
          "poster": "SYSTEM",
          "editor": null,
          "source": "",
          "title": "Status Changed",
          "body": "Closed by user",
          "message": {
            "body": "Closed by user",
            "type": "html",
            "stripped_images": [],
            "embedded_images": [],
            "options": {
              "strip-embedded": false,
              "balanced": true
            }
          },
          "format": "html",
          "created": "2018-11-16 13:23:21",
          "updated": "0000-00-00 00:00:00",
          "staff_name": null,
          "user_name": null
        },
        {
          "id": 282,
          "pid": 0,
          "thread_id": 104,
          "staff_id": null,
          "user_id": null,
          "type": "M",
          "poster": "",
          "editor": null,
          "source": "",
          "title": null,
          "body": "My updated message using user_id",
          "message": {
            "body": "My updated message using user_id",
            "type": "html",
            "stripped_images": [],
            "embedded_images": [],
            "options": {
              "strip-embedded": false,
              "balanced": true
            }
          },
          "format": "html",
          "created": "2018-11-16 13:23:22",
          "updated": "0000-00-00 00:00:00",
          "staff_name": null,
          "user_name": null
        },
        {
          "id": 283,
          "pid": 0,
          "thread_id": 104,
          "staff_id": null,
          "user_id": null,
          "type": "M",
          "poster": "",
          "editor": null,
          "source": "",
          "title": null,
          "body": "My updated message using email",
          "message": {
            "body": "My updated message using email",
            "type": "html",
            "stripped_images": [],
            "embedded_images": [],
            "options": {
              "strip-embedded": false,
              "balanced": true
            }
          },
          "format": "html",
          "created": "2018-11-16 13:23:22",
          "updated": "0000-00-00 00:00:00",
          "staff_name": null,
          "user_name": null
        }
      ]
    },
    {
      "ticket_number": "458068",
      "subject": "Testing API",
      "ticket_status": "Open",
      "statusId": 1,
      "priority": "Low",
      "department": "Support",
      "create_timestamp": "2018-11-16 13:23:23",
      "user": {
        "fullname": "Michael User2",
        "firstname": "Michael",
        "lastname": "User2",
        "email": "theodog.test@gmail.com",
        "phone": ""
      },
      "source": "API",
      "due_timestamp": "2018-11-18 13:23:23",
      "close_timestamp": null,
      "topic": "Feedback",
      "topicId": 2,
      "last_message_timestamp": "2018-11-16 13:23:23",
      "last_response_timestamp": null,
      "assigned_to": [],
      "threads": [
        {
          "id": 284,
          "pid": 0,
          "thread_id": 105,
          "staff_id": null,
          "user_id": 5,
          "type": "M",
          "poster": "Michael User2",
          "editor": null,
          "source": "API",
          "title": "Testing API",
          "body": "My original message",
          "message": {
            "body": "My original message",
            "type": "text",
            "stripped_images": [],
            "embedded_images": [],
            "options": {
              "strip-embedded": true
            }
          },
          "format": "text",
          "created": "2018-11-16 13:23:23",
          "updated": "0000-00-00 00:00:00",
          "staff_name": null,
          "user_name": {
            "format": "original",
            "parts": {
              "salutation": "",
              "first": "Michael",
              "suffix": "",
              "last": "User2",
              "middle": ""
            },
            "name": "Michael User2"
          }
        }
      ]
    },
    {
      "ticket_number": "888010",
      "subject": "Testing API",
      "ticket_status": "Open",
      "statusId": 1,
      "priority": "Low",
      "department": "Support",
      "create_timestamp": "2018-11-16 13:23:23",
      "user": {
        "fullname": "Michael User2",
        "firstname": "Michael",
        "lastname": "User2",
        "email": "theodog.test@gmail.com",
        "phone": ""
      },
      "source": "API",
      "due_timestamp": "2018-11-18 13:23:23",
      "close_timestamp": null,
      "topic": "Feedback",
      "topicId": 2,
      "last_message_timestamp": "2018-11-16 13:23:23",
      "last_response_timestamp": null,
      "assigned_to": [],
      "threads": [
        {
          "id": 285,
          "pid": 0,
          "thread_id": 106,
          "staff_id": null,
          "user_id": 5,
          "type": "M",
          "poster": "Michael User2",
          "editor": null,
          "source": "API",
          "title": "Testing API",
          "body": "My original message",
          "message": {
            "body": "My original message",
            "type": "text",
            "stripped_images": [],
            "embedded_images": [],
            "options": {
              "strip-embedded": true
            }
          },
          "format": "text",
          "created": "2018-11-16 13:23:23",
          "updated": "0000-00-00 00:00:00",
          "staff_name": null,
          "user_name": {
            "format": "original",
            "parts": {
              "salutation": "",
              "first": "Michael",
              "suffix": "",
              "last": "User2",
              "middle": ""
            },
            "name": "Michael User2"
          }
        }
      ]
    }
  ],
  "status_code": "0",
  "status_msg": "success"
}

post reply to ticket with ticket new status
POST /api/scp/tickets/reply.json

params:

{
  "ticketNumber": 112356,
  "msgId": "",
  "a": "reply",
  "emailreply": "1",
  "emailcollab": "1",
  "cannedResp": "0",
  "draft_id": "",
  "response": "ticket issue is resolved!",
  "signature": "none",
  "reply_status_id": "1",
  "staffUserName": "Michael",
  "ip_address": "::1",
  "cannedattachments": ""
}
Status: success

Response:

{
  "status_code": "0",
  "status_msg": "reply posted successfully"
}

restGetTickets
GET /api/scp/tickets.json

params:

{}
Status: success

Response:

[
  [
    [
      "number",
      "112356"
    ],
    [
      "created",
      "2018-11-16 13:21:53"
    ],
    [
      "updated",
      "2018-11-16 13:23:25"
    ],
    [
      "closed",
      "2018-11-16 13:23:22"
    ],
    [
      "href",
      "/api/tickets/112356"
    ]
  ],
  [
    [
      "number",
      "458068"
    ],
    [
      "created",
      "2018-11-16 13:23:23"
    ],
    [
      "updated",
      "2018-11-16 13:23:23"
    ],
    [
      "closed",
      null
    ],
    [
      "href",
      "/api/tickets/458068"
    ]
  ],
  [
    [
      "number",
      "888010"
    ],
    [
      "created",
      "2018-11-16 13:23:23"
    ],
    [
      "updated",
      "2018-11-16 13:23:23"
    ],
    [
      "closed",
      null
    ],
    [
      "href",
      "/api/tickets/888010"
    ]
  ]
]
```