create table ldbf_mailform_request
(
    id         int auto_increment
        primary key,
    secretCode varchar(9)                         not null,
    email      varchar(200)                       not null,
    mailBody   TEXT                               not null,
    confirmed  bool     default 0                 not null,
    created    datetime default CURRENT_TIMESTAMP not null,
    modified   datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

