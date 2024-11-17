create table geelhoed_webshop_product
(
    id             int auto_increment
        primary key,
    parentId       int                                null,
    name           varchar(255)                       not null,
    description    text                               not null,
    options        json                               not null,
    euroPrice      double                             null,
    gcaTicketPrice int                                null,
    created        datetime default CURRENT_TIMESTAMP not null,
    modified       datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_webshop_product_geelhoed_webshop_product_id_fk
        foreign key (parentId) references geelhoed_webshop_product (id)
            on update cascade
);

create index geelhoed_webshop_product_name_index
    on geelhoed_webshop_product (name);

create table geelhoed_webshop_order
(
    id         int auto_increment
        primary key,
    subscriberId int                                                                                 not null,
    locationId int                                                                                   not null,
    day        tinyint                                                                               not null,
    status     enum ('quote', 'pending_ticket_check', 'pending_payment', 'in_progress', 'delivered') not null,
    created    datetime default CURRENT_TIMESTAMP                                                    not null,
    modified   datetime default CURRENT_TIMESTAMP                                                    not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_webshop_order_locations_id_fk
        foreign key (locationId) references locations (id)
            on update cascade
);

create index geelhoed_webshop_order_locationId_day_index
    on geelhoed_webshop_order (locationId, day);

create index geelhoed_webshop_order_locationId_index
    on geelhoed_webshop_order (locationId);

alter table geelhoed_webshop_order
    add constraint geelhoed_webshop_order___fk
        foreign key (subscriberId) references `geelhoed_clubactie_subscriber` (id);

alter table geelhoed_webshop_order
    add paymentId varchar(64) not null default '' after status;
