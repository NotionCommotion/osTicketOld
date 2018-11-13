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
3. Add API to add client user.
4. Clean up threads.
5. Changes to allow specific user name to be logged as making changes instead of "SYSTEM".
6. Better utilize exising osTicket methods where applicable.
7. Figure out how to make this a pull request from the osTicket master.

## Demo

/api/api_tester.php will execute each method the response can be viewed using your browser inspector.  Change the API key, email, and user name to reflect your installation.


## Usage

Perform curl requests

```
GET /api/tickets.json: {
    "email": "theodog.test@gmail.com"
}
response:
[{
        "id": 0,
        "value": {
            "ticket_number": "896164",
            "subject": "A new ticket",
            "ticket_status": "Open",
            "statusId": 1,
            "priority": "Low",
            "department": "Support",
            "create_timestamp": "2018-11-13 13:34:25",
            "user": {
                "fullname": "Michael User2",
                "firstname": "Michael",
                "lastname": "User2",
                "email": "theodog.test@gmail.com",
                "phone": ""
            },
            "source": "Web",
            "due_timestamp": "2018-11-15 13:34:25",
            "close_timestamp": "2018-11-13 13:50:32",
            "topic": "Feedback",
            "topicId": 2,
            "last_message_timestamp": "2018-11-13 13:50:33",
            "last_response_timestamp": "2018-11-13 13:50:35",
            "assigned_to": [{
                    "format": "full",
                    "parts": {
                        "first": "Michael",
                        "last": "Reed"
                    },
                    "name": "Michael Reed"
                }
            ],
            "threads": [{
                    "id": 96,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": 5,
                    "type": "M",
                    "poster": "Michael User2",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is my original new ticket.",
                    "message": {
                        "body": "This is my original new ticket.",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:34:25",
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
                }, {
                    "id": 97,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": 5,
                    "type": "M",
                    "poster": "Michael User2",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is a response by the user to the new ticket.<br /><br /> ",
                    "message": {
                        "body": "This is a response by the user to the new ticket.<br /><br /> ",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:34:45",
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
                }, {
                    "id": 98,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": 1,
                    "user_id": null,
                    "type": "R",
                    "poster": "Michael Reed",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                    "message": {
                        "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:47:47",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": {
                        "format": "full",
                        "parts": {
                            "first": "Michael",
                            "last": "Reed"
                        },
                        "name": "Michael Reed"
                    },
                    "user_name": null
                }, {
                    "id": 99,
                    "pid": 0,
                    "thread_id": 46,
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
                    "created": "2018-11-13 13:50:32",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 100,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": null,
                    "type": "M",
                    "poster": "",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "My updated message",
                    "message": {
                        "body": "My updated message",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:50:33",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 102,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": 1,
                    "user_id": null,
                    "type": "R",
                    "poster": "Michael Reed",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "ticket issue is resolved !",
                    "message": {
                        "body": "ticket issue is resolved !",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:50:35",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": {
                        "format": "full",
                        "parts": {
                            "first": "Michael",
                            "last": "Reed"
                        },
                        "name": "Michael Reed"
                    },
                    "user_name": null
                }
            ]
        }
    }, {
        "id": 1,
        "value": {
            "ticket_number": "542343",
            "subject": "Testing API",
            "ticket_status": "Open",
            "statusId": 1,
            "priority": "Low",
            "department": "Support",
            "create_timestamp": "2018-11-13 13:50:33",
            "user": {
                "fullname": "Michael User2",
                "firstname": "Michael",
                "lastname": "User2",
                "email": "theodog.test@gmail.com",
                "phone": ""
            },
            "source": "API",
            "due_timestamp": "2018-11-15 13:50:33",
            "close_timestamp": null,
            "topic": "Feedback",
            "topicId": 2,
            "last_message_timestamp": "2018-11-13 13:50:33",
            "last_response_timestamp": null,
            "assigned_to": [],
            "threads": [{
                    "id": 101,
                    "pid": 0,
                    "thread_id": 47,
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
                    "created": "2018-11-13 13:50:33",
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
    }
]

GET /api/topics.json: {}
response:
[{
        "id": 2,
        "value": "Feedback"
    }, {
        "id": 1,
        "value": "General Inquiry"
    }, {
        "id": 10,
        "value": "Report a Problem"
    }, {
        "id": 11,
        "value": "Report a Problem/Access Issue"
    }
]

GET /api/tickets.json/896164: {}
response: {
    "ticket_number": "896164",
    "subject": "A new ticket",
    "ticket_status": "Open",
    "statusId": 1,
    "priority": "Low",
    "department": "Support",
    "create_timestamp": "2018-11-13 13:34:25",
    "user": {
        "fullname": "Michael User2",
        "firstname": "Michael",
        "lastname": "User2",
        "email": "theodog.test@gmail.com",
        "phone": ""
    },
    "source": "Web",
    "due_timestamp": "2018-11-15 13:34:25",
    "close_timestamp": "2018-11-13 13:50:32",
    "topic": "Feedback",
    "topicId": 2,
    "last_message_timestamp": "2018-11-13 13:50:33",
    "last_response_timestamp": "2018-11-13 13:50:35",
    "assigned_to": [{
            "format": "full",
            "parts": {
                "first": "Michael",
                "last": "Reed"
            },
            "name": "Michael Reed"
        }
    ],
    "threads": [{
            "id": 96,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": 5,
            "type": "M",
            "poster": "Michael User2",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is my original new ticket.",
            "message": {
                "body": "This is my original new ticket.",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:34:25",
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
        }, {
            "id": 97,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": 5,
            "type": "M",
            "poster": "Michael User2",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is a response by the user to the new ticket.<br /><br /> ",
            "message": {
                "body": "This is a response by the user to the new ticket.<br /><br /> ",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:34:45",
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
        }, {
            "id": 98,
            "pid": 0,
            "thread_id": 46,
            "staff_id": 1,
            "user_id": null,
            "type": "R",
            "poster": "Michael Reed",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
            "message": {
                "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:47:47",
            "updated": "0000-00-00 00:00:00",
            "staff_name": {
                "format": "full",
                "parts": {
                    "first": "Michael",
                    "last": "Reed"
                },
                "name": "Michael Reed"
            },
            "user_name": null
        }, {
            "id": 99,
            "pid": 0,
            "thread_id": 46,
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
            "created": "2018-11-13 13:50:32",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }, {
            "id": 100,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": null,
            "type": "M",
            "poster": "",
            "editor": null,
            "source": "",
            "title": null,
            "body": "My updated message",
            "message": {
                "body": "My updated message",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:50:33",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }, {
            "id": 102,
            "pid": 0,
            "thread_id": 46,
            "staff_id": 1,
            "user_id": null,
            "type": "R",
            "poster": "Michael Reed",
            "editor": null,
            "source": "",
            "title": null,
            "body": "ticket issue is resolved !",
            "message": {
                "body": "ticket issue is resolved !",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:50:35",
            "updated": "0000-00-00 00:00:00",
            "staff_name": {
                "format": "full",
                "parts": {
                    "first": "Michael",
                    "last": "Reed"
                },
                "name": "Michael Reed"
            },
            "user_name": null
        }
    ]
}

DELETE /api/tickets.json/896164: {
    "email": "theodog.test@gmail.com"
}
response:
undefined

POST /api/tickets.json/896164: {
    "email": "theodog.test@gmail.com"
}
response: {
    "ticket_number": "896164",
    "subject": "A new ticket",
    "ticket_status": "Open",
    "statusId": 1,
    "priority": "Low",
    "department": "Support",
    "create_timestamp": "2018-11-13 13:34:25",
    "user": {
        "fullname": "Michael User2",
        "firstname": "Michael",
        "lastname": "User2",
        "email": "theodog.test@gmail.com",
        "phone": ""
    },
    "source": "Web",
    "due_timestamp": "2018-11-15 13:34:25",
    "close_timestamp": "2018-11-13 14:03:19",
    "topic": "Feedback",
    "topicId": 2,
    "last_message_timestamp": "2018-11-13 13:50:33",
    "last_response_timestamp": "2018-11-13 13:50:35",
    "assigned_to": [{
            "format": "full",
            "parts": {
                "first": "Michael",
                "last": "Reed"
            },
            "name": "Michael Reed"
        }
    ],
    "threads": [{
            "id": 96,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": 5,
            "type": "M",
            "poster": "Michael User2",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is my original new ticket.",
            "message": {
                "body": "This is my original new ticket.",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:34:25",
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
        }, {
            "id": 97,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": 5,
            "type": "M",
            "poster": "Michael User2",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is a response by the user to the new ticket.<br /><br /> ",
            "message": {
                "body": "This is a response by the user to the new ticket.<br /><br /> ",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:34:45",
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
        }, {
            "id": 98,
            "pid": 0,
            "thread_id": 46,
            "staff_id": 1,
            "user_id": null,
            "type": "R",
            "poster": "Michael Reed",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
            "message": {
                "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:47:47",
            "updated": "0000-00-00 00:00:00",
            "staff_name": {
                "format": "full",
                "parts": {
                    "first": "Michael",
                    "last": "Reed"
                },
                "name": "Michael Reed"
            },
            "user_name": null
        }, {
            "id": 99,
            "pid": 0,
            "thread_id": 46,
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
            "created": "2018-11-13 13:50:32",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }, {
            "id": 100,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": null,
            "type": "M",
            "poster": "",
            "editor": null,
            "source": "",
            "title": null,
            "body": "My updated message",
            "message": {
                "body": "My updated message",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:50:33",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }, {
            "id": 102,
            "pid": 0,
            "thread_id": 46,
            "staff_id": 1,
            "user_id": null,
            "type": "R",
            "poster": "Michael Reed",
            "editor": null,
            "source": "",
            "title": null,
            "body": "ticket issue is resolved !",
            "message": {
                "body": "ticket issue is resolved !",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:50:35",
            "updated": "0000-00-00 00:00:00",
            "staff_name": {
                "format": "full",
                "parts": {
                    "first": "Michael",
                    "last": "Reed"
                },
                "name": "Michael Reed"
            },
            "user_name": null
        }, {
            "id": 103,
            "pid": 0,
            "thread_id": 46,
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
            "created": "2018-11-13 14:03:19",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }
    ]
}

PUT /api/tickets.json/896164: {
    "email": "theodog.test@gmail.com",
    "message": "My updated message"
}
response: {
    "ticket_number": "896164",
    "subject": "A new ticket",
    "ticket_status": "Open",
    "statusId": 1,
    "priority": "Low",
    "department": "Support",
    "create_timestamp": "2018-11-13 13:34:25",
    "user": {
        "fullname": "Michael User2",
        "firstname": "Michael",
        "lastname": "User2",
        "email": "theodog.test@gmail.com",
        "phone": ""
    },
    "source": "Web",
    "due_timestamp": "2018-11-15 13:34:25",
    "close_timestamp": "2018-11-13 14:03:19",
    "topic": "Feedback",
    "topicId": 2,
    "last_message_timestamp": "2018-11-13 14:03:20",
    "last_response_timestamp": "2018-11-13 13:50:35",
    "assigned_to": [{
            "format": "full",
            "parts": {
                "first": "Michael",
                "last": "Reed"
            },
            "name": "Michael Reed"
        }
    ],
    "threads": [{
            "id": 96,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": 5,
            "type": "M",
            "poster": "Michael User2",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is my original new ticket.",
            "message": {
                "body": "This is my original new ticket.",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:34:25",
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
        }, {
            "id": 97,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": 5,
            "type": "M",
            "poster": "Michael User2",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is a response by the user to the new ticket.<br /><br /> ",
            "message": {
                "body": "This is a response by the user to the new ticket.<br /><br /> ",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:34:45",
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
        }, {
            "id": 98,
            "pid": 0,
            "thread_id": 46,
            "staff_id": 1,
            "user_id": null,
            "type": "R",
            "poster": "Michael Reed",
            "editor": null,
            "source": "",
            "title": null,
            "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
            "message": {
                "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:47:47",
            "updated": "0000-00-00 00:00:00",
            "staff_name": {
                "format": "full",
                "parts": {
                    "first": "Michael",
                    "last": "Reed"
                },
                "name": "Michael Reed"
            },
            "user_name": null
        }, {
            "id": 99,
            "pid": 0,
            "thread_id": 46,
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
            "created": "2018-11-13 13:50:32",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }, {
            "id": 100,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": null,
            "type": "M",
            "poster": "",
            "editor": null,
            "source": "",
            "title": null,
            "body": "My updated message",
            "message": {
                "body": "My updated message",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:50:33",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }, {
            "id": 102,
            "pid": 0,
            "thread_id": 46,
            "staff_id": 1,
            "user_id": null,
            "type": "R",
            "poster": "Michael Reed",
            "editor": null,
            "source": "",
            "title": null,
            "body": "ticket issue is resolved !",
            "message": {
                "body": "ticket issue is resolved !",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 13:50:35",
            "updated": "0000-00-00 00:00:00",
            "staff_name": {
                "format": "full",
                "parts": {
                    "first": "Michael",
                    "last": "Reed"
                },
                "name": "Michael Reed"
            },
            "user_name": null
        }, {
            "id": 103,
            "pid": 0,
            "thread_id": 46,
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
            "created": "2018-11-13 14:03:19",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }, {
            "id": 104,
            "pid": 0,
            "thread_id": 46,
            "staff_id": null,
            "user_id": null,
            "type": "M",
            "poster": "",
            "editor": null,
            "source": "",
            "title": null,
            "body": "My updated message",
            "message": {
                "body": "My updated message",
                "type": "html",
                "stripped_images": [],
                "embedded_images": [],
                "options": {
                    "strip-embedded": false,
                    "balanced": true
                }
            },
            "format": "html",
            "created": "2018-11-13 14:03:20",
            "updated": "0000-00-00 00:00:00",
            "staff_name": null,
            "user_name": null
        }
    ]
}

POST /api/tickets.json: {
    "email": "theodog.test@gmail.com",
    "message": "My original message",
    "name": "John Doe",
    "subject": "Testing API",
    "topicId": 2
}
response: {
    "ticket_number": "407896",
    "subject": "Testing API",
    "ticket_status": "Open",
    "statusId": 1,
    "priority": "Low",
    "department": "Support",
    "create_timestamp": "2018-11-13 14:03:20",
    "user": {
        "fullname": "Michael User2",
        "firstname": "Michael",
        "lastname": "User2",
        "email": "theodog.test@gmail.com",
        "phone": ""
    },
    "source": "API",
    "due_timestamp": "2018-11-15 14:03:20",
    "close_timestamp": null,
    "topic": "Feedback",
    "topicId": 2,
    "last_message_timestamp": "2018-11-13 14:03:20",
    "last_response_timestamp": null,
    "assigned_to": [],
    "threads": [{
            "id": 105,
            "pid": 0,
            "thread_id": 48,
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
            "created": "2018-11-13 14:03:20",
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

GET /api/scp/tickets/ticketInfo.json: {
    "ticketNumber": 896164
}
response: {
    "ticket": {
        "ticket_number": "896164",
        "subject": "A new ticket",
        "ticket_status": "Open",
        "statusId": 1,
        "priority": "Low",
        "department": "Support",
        "create_timestamp": "2018-11-13 13:34:25",
        "user": {
            "fullname": "Michael User2",
            "firstname": "Michael",
            "lastname": "User2",
            "email": "theodog.test@gmail.com",
            "phone": ""
        },
        "source": "Web",
        "due_timestamp": "2018-11-15 13:34:25",
        "close_timestamp": "2018-11-13 14:03:19",
        "topic": "Feedback",
        "topicId": 2,
        "last_message_timestamp": "2018-11-13 14:03:20",
        "last_response_timestamp": "2018-11-13 13:50:35",
        "assigned_to": [{
                "format": "full",
                "parts": {
                    "first": "Michael",
                    "last": "Reed"
                },
                "name": "Michael Reed"
            }
        ],
        "threads": [{
                "id": 96,
                "pid": 0,
                "thread_id": 46,
                "staff_id": null,
                "user_id": 5,
                "type": "M",
                "poster": "Michael User2",
                "editor": null,
                "source": "",
                "title": null,
                "body": "This is my original new ticket.",
                "message": {
                    "body": "This is my original new ticket.",
                    "type": "html",
                    "stripped_images": [],
                    "embedded_images": [],
                    "options": {
                        "strip-embedded": false,
                        "balanced": true
                    }
                },
                "format": "html",
                "created": "2018-11-13 13:34:25",
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
            }, {
                "id": 97,
                "pid": 0,
                "thread_id": 46,
                "staff_id": null,
                "user_id": 5,
                "type": "M",
                "poster": "Michael User2",
                "editor": null,
                "source": "",
                "title": null,
                "body": "This is a response by the user to the new ticket.<br /><br /> ",
                "message": {
                    "body": "This is a response by the user to the new ticket.<br /><br /> ",
                    "type": "html",
                    "stripped_images": [],
                    "embedded_images": [],
                    "options": {
                        "strip-embedded": false,
                        "balanced": true
                    }
                },
                "format": "html",
                "created": "2018-11-13 13:34:45",
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
            }, {
                "id": 98,
                "pid": 0,
                "thread_id": 46,
                "staff_id": 1,
                "user_id": null,
                "type": "R",
                "poster": "Michael Reed",
                "editor": null,
                "source": "",
                "title": null,
                "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                "message": {
                    "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                    "type": "html",
                    "stripped_images": [],
                    "embedded_images": [],
                    "options": {
                        "strip-embedded": false,
                        "balanced": true
                    }
                },
                "format": "html",
                "created": "2018-11-13 13:47:47",
                "updated": "0000-00-00 00:00:00",
                "staff_name": {
                    "format": "full",
                    "parts": {
                        "first": "Michael",
                        "last": "Reed"
                    },
                    "name": "Michael Reed"
                },
                "user_name": null
            }, {
                "id": 99,
                "pid": 0,
                "thread_id": 46,
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
                "created": "2018-11-13 13:50:32",
                "updated": "0000-00-00 00:00:00",
                "staff_name": null,
                "user_name": null
            }, {
                "id": 100,
                "pid": 0,
                "thread_id": 46,
                "staff_id": null,
                "user_id": null,
                "type": "M",
                "poster": "",
                "editor": null,
                "source": "",
                "title": null,
                "body": "My updated message",
                "message": {
                    "body": "My updated message",
                    "type": "html",
                    "stripped_images": [],
                    "embedded_images": [],
                    "options": {
                        "strip-embedded": false,
                        "balanced": true
                    }
                },
                "format": "html",
                "created": "2018-11-13 13:50:33",
                "updated": "0000-00-00 00:00:00",
                "staff_name": null,
                "user_name": null
            }, {
                "id": 102,
                "pid": 0,
                "thread_id": 46,
                "staff_id": 1,
                "user_id": null,
                "type": "R",
                "poster": "Michael Reed",
                "editor": null,
                "source": "",
                "title": null,
                "body": "ticket issue is resolved !",
                "message": {
                    "body": "ticket issue is resolved !",
                    "type": "html",
                    "stripped_images": [],
                    "embedded_images": [],
                    "options": {
                        "strip-embedded": false,
                        "balanced": true
                    }
                },
                "format": "html",
                "created": "2018-11-13 13:50:35",
                "updated": "0000-00-00 00:00:00",
                "staff_name": {
                    "format": "full",
                    "parts": {
                        "first": "Michael",
                        "last": "Reed"
                    },
                    "name": "Michael Reed"
                },
                "user_name": null
            }, {
                "id": 103,
                "pid": 0,
                "thread_id": 46,
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
                "created": "2018-11-13 14:03:19",
                "updated": "0000-00-00 00:00:00",
                "staff_name": null,
                "user_name": null
            }, {
                "id": 104,
                "pid": 0,
                "thread_id": 46,
                "staff_id": null,
                "user_id": null,
                "type": "M",
                "poster": "",
                "editor": null,
                "source": "",
                "title": null,
                "body": "My updated message",
                "message": {
                    "body": "My updated message",
                    "type": "html",
                    "stripped_images": [],
                    "embedded_images": [],
                    "options": {
                        "strip-embedded": false,
                        "balanced": true
                    }
                },
                "format": "html",
                "created": "2018-11-13 14:03:20",
                "updated": "0000-00-00 00:00:00",
                "staff_name": null,
                "user_name": null
            }
        ]
    },
    "status_code": "0",
    "status_msg": "ticket details retrieved successfully"
}

GET /api/scp/tickets/staffTickets.json: {
    "staffUserName": "Michael"
}
response: {
    "tickets": [{
            "ticket_number": "896164",
            "subject": "A new ticket",
            "ticket_status": "Open",
            "statusId": 1,
            "priority": "Low",
            "department": "Support",
            "create_timestamp": "2018-11-13 13:34:25",
            "user": {
                "fullname": "Michael User2",
                "firstname": "Michael",
                "lastname": "User2",
                "email": "theodog.test@gmail.com",
                "phone": ""
            },
            "source": "Web",
            "due_timestamp": "2018-11-15 13:34:25",
            "close_timestamp": "2018-11-13 14:03:19",
            "topic": "Feedback",
            "topicId": 2,
            "last_message_timestamp": "2018-11-13 14:03:20",
            "last_response_timestamp": "2018-11-13 13:50:35",
            "assigned_to": [{
                    "format": "full",
                    "parts": {
                        "first": "Michael",
                        "last": "Reed"
                    },
                    "name": "Michael Reed"
                }
            ],
            "threads": [{
                    "id": 96,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": 5,
                    "type": "M",
                    "poster": "Michael User2",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is my original new ticket.",
                    "message": {
                        "body": "This is my original new ticket.",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:34:25",
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
                }, {
                    "id": 97,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": 5,
                    "type": "M",
                    "poster": "Michael User2",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is a response by the user to the new ticket.<br /><br /> ",
                    "message": {
                        "body": "This is a response by the user to the new ticket.<br /><br /> ",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:34:45",
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
                }, {
                    "id": 98,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": 1,
                    "user_id": null,
                    "type": "R",
                    "poster": "Michael Reed",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                    "message": {
                        "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:47:47",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": {
                        "format": "full",
                        "parts": {
                            "first": "Michael",
                            "last": "Reed"
                        },
                        "name": "Michael Reed"
                    },
                    "user_name": null
                }, {
                    "id": 99,
                    "pid": 0,
                    "thread_id": 46,
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
                    "created": "2018-11-13 13:50:32",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 100,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": null,
                    "type": "M",
                    "poster": "",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "My updated message",
                    "message": {
                        "body": "My updated message",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:50:33",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 102,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": 1,
                    "user_id": null,
                    "type": "R",
                    "poster": "Michael Reed",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "ticket issue is resolved !",
                    "message": {
                        "body": "ticket issue is resolved !",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:50:35",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": {
                        "format": "full",
                        "parts": {
                            "first": "Michael",
                            "last": "Reed"
                        },
                        "name": "Michael Reed"
                    },
                    "user_name": null
                }, {
                    "id": 103,
                    "pid": 0,
                    "thread_id": 46,
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
                    "created": "2018-11-13 14:03:19",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 104,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": null,
                    "type": "M",
                    "poster": "",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "My updated message",
                    "message": {
                        "body": "My updated message",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 14:03:20",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }
            ]
        }
    ],
    "status_code": "0",
    "status_msg": "success"
}

GET /api/scp/tickets/clientTickets.json: {
    "clientUserMail": "theodog.test@gmail.com"
}
response: {
    "tickets": [{
            "ticket_number": "896164",
            "subject": "A new ticket",
            "ticket_status": "Open",
            "statusId": 1,
            "priority": "Low",
            "department": "Support",
            "create_timestamp": "2018-11-13 13:34:25",
            "user": {
                "fullname": "Michael User2",
                "firstname": "Michael",
                "lastname": "User2",
                "email": "theodog.test@gmail.com",
                "phone": ""
            },
            "source": "Web",
            "due_timestamp": "2018-11-15 13:34:25",
            "close_timestamp": "2018-11-13 14:03:19",
            "topic": "Feedback",
            "topicId": 2,
            "last_message_timestamp": "2018-11-13 14:03:20",
            "last_response_timestamp": "2018-11-13 13:50:35",
            "assigned_to": [{
                    "format": "full",
                    "parts": {
                        "first": "Michael",
                        "last": "Reed"
                    },
                    "name": "Michael Reed"
                }
            ],
            "threads": [{
                    "id": 96,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": 5,
                    "type": "M",
                    "poster": "Michael User2",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is my original new ticket.",
                    "message": {
                        "body": "This is my original new ticket.",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:34:25",
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
                }, {
                    "id": 97,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": 5,
                    "type": "M",
                    "poster": "Michael User2",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is a response by the user to the new ticket.<br /><br /> ",
                    "message": {
                        "body": "This is a response by the user to the new ticket.<br /><br /> ",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:34:45",
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
                }, {
                    "id": 98,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": 1,
                    "user_id": null,
                    "type": "R",
                    "poster": "Michael Reed",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                    "message": {
                        "body": "This is a reply to the first message.<br />The original and client's first response were through the standard osticket interface.",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:47:47",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": {
                        "format": "full",
                        "parts": {
                            "first": "Michael",
                            "last": "Reed"
                        },
                        "name": "Michael Reed"
                    },
                    "user_name": null
                }, {
                    "id": 99,
                    "pid": 0,
                    "thread_id": 46,
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
                    "created": "2018-11-13 13:50:32",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 100,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": null,
                    "type": "M",
                    "poster": "",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "My updated message",
                    "message": {
                        "body": "My updated message",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:50:33",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 102,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": 1,
                    "user_id": null,
                    "type": "R",
                    "poster": "Michael Reed",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "ticket issue is resolved !",
                    "message": {
                        "body": "ticket issue is resolved !",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 13:50:35",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": {
                        "format": "full",
                        "parts": {
                            "first": "Michael",
                            "last": "Reed"
                        },
                        "name": "Michael Reed"
                    },
                    "user_name": null
                }, {
                    "id": 103,
                    "pid": 0,
                    "thread_id": 46,
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
                    "created": "2018-11-13 14:03:19",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }, {
                    "id": 104,
                    "pid": 0,
                    "thread_id": 46,
                    "staff_id": null,
                    "user_id": null,
                    "type": "M",
                    "poster": "",
                    "editor": null,
                    "source": "",
                    "title": null,
                    "body": "My updated message",
                    "message": {
                        "body": "My updated message",
                        "type": "html",
                        "stripped_images": [],
                        "embedded_images": [],
                        "options": {
                            "strip-embedded": false,
                            "balanced": true
                        }
                    },
                    "format": "html",
                    "created": "2018-11-13 14:03:20",
                    "updated": "0000-00-00 00:00:00",
                    "staff_name": null,
                    "user_name": null
                }
            ]
        }, {
            "ticket_number": "542343",
            "subject": "Testing API",
            "ticket_status": "Open",
            "statusId": 1,
            "priority": "Low",
            "department": "Support",
            "create_timestamp": "2018-11-13 13:50:33",
            "user": {
                "fullname": "Michael User2",
                "firstname": "Michael",
                "lastname": "User2",
                "email": "theodog.test@gmail.com",
                "phone": ""
            },
            "source": "API",
            "due_timestamp": "2018-11-15 13:50:33",
            "close_timestamp": null,
            "topic": "Feedback",
            "topicId": 2,
            "last_message_timestamp": "2018-11-13 13:50:33",
            "last_response_timestamp": null,
            "assigned_to": [],
            "threads": [{
                    "id": 101,
                    "pid": 0,
                    "thread_id": 47,
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
                    "created": "2018-11-13 13:50:33",
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
        }, {
            "ticket_number": "407896",
            "subject": "Testing API",
            "ticket_status": "Open",
            "statusId": 1,
            "priority": "Low",
            "department": "Support",
            "create_timestamp": "2018-11-13 14:03:20",
            "user": {
                "fullname": "Michael User2",
                "firstname": "Michael",
                "lastname": "User2",
                "email": "theodog.test@gmail.com",
                "phone": ""
            },
            "source": "API",
            "due_timestamp": "2018-11-15 14:03:20",
            "close_timestamp": null,
            "topic": "Feedback",
            "topicId": 2,
            "last_message_timestamp": "2018-11-13 14:03:20",
            "last_response_timestamp": null,
            "assigned_to": [],
            "threads": [{
                    "id": 105,
                    "pid": 0,
                    "thread_id": 48,
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
                    "created": "2018-11-13 14:03:20",
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

POST /api/scp/tickets/reply.json: {
    "ticketNumber": 896164,
    "msgId": "",
    "a": "reply",
    "emailreply": "1",
    "emailcollab": "1",
    "cannedResp": "0",
    "draft_id": "",
    "response": "ticket issue is resolved !",
    "signature": "none",
    "reply_status_id": "1",
    "staffUserName": "Michael",
    "ip_address": "::1",
    "cannedattachments": ""
}
response: {
    "status_code": "0",
    "status_msg": "reply posted successfully"
}
```