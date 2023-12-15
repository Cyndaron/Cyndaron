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

create index geelhoed_volunteer_tot_end
    on geelhoed_volunteer_tot (end);

create index geelhoed_volunteer_tot_start
    on geelhoed_volunteer_tot (start);

create index geelhoed_volunteer_tot_start_end
    on geelhoed_volunteer_tot (start, end);


create table geelhoed_volunteer_tot_participation
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
    constraint geelhoed_volunteer_tot_participation_id_fk
        foreign key (eventId) references geelhoed_volunteer_tot (id)
            on update cascade on delete cascade
);

create index geelhoed_volunteer_tot_participation_email
    on geelhoed_volunteer_tot_participation (email);

create index geelhoed_volunteer_tot_participation_et
    on geelhoed_volunteer_tot_participation (eventId, type);

create index geelhoed_volunteer_tot_participation_eventId
    on geelhoed_volunteer_tot_participation (eventId);

alter table geelhoed_volunteer_tot_participation
    add phone varchar(30) not null after email;

alter table geelhoed_volunteer_tot
    add photoalbumLink varchar(255) not null after data;
