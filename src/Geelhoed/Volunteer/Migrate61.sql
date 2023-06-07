create table geelhoed_volunteer_event
(
    id       int auto_increment
        primary key,
    name     varchar(200)                       not null,
    start    datetime                           not null,
    end      datetime                           not null,
    data     json                               not null,
    created  datetime default CURRENT_TIMESTAMP not null,
    modified datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create index geelhoed_volunteer_event_end
    on geelhoed_volunteer_event (end);

create index geelhoed_volunteer_event_start
    on geelhoed_volunteer_event (start);

create index geelhoed_volunteer_event_start_end
    on geelhoed_volunteer_event (start, end);


create table geelhoed_volunteer_event_participation
(
    id       int auto_increment
        primary key,
    eventId  int                                not null,
    name     varchar(200)                       not null,
    email    varchar(200)                       not null,
    type     varchar(200)                       not null,
    data     json                               not null,
    comments text                               not null,
    created  datetime default CURRENT_TIMESTAMP not null,
    modified datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_volunteer_event_participation_id_fk
        foreign key (eventId) references geelhoed_volunteer_event (id)
            on update cascade on delete cascade
);

create index geelhoed_volunteer_event_participation_email
    on geelhoed_volunteer_event_participation (email);

create index geelhoed_volunteer_event_participation_et
    on geelhoed_volunteer_event_participation (eventId, type);

create index geelhoed_volunteer_event_participation_eventId
    on geelhoed_volunteer_event_participation (eventId);

alter table geelhoed_volunteer_event_participation
    add phone varchar(30) not null after email;
