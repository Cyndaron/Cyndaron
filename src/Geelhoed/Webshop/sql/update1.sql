alter table geelhoed_webshop_order
    drop column day;

alter table geelhoed_webshop_order
    drop foreign key geelhoed_webshop_order_locations_id_fk;

alter table geelhoed_webshop_order
    drop column locationId;

alter table geelhoed_webshop_order
    add hourId int not null default 1 after subscriberId;

alter table geelhoed_webshop_order
    add constraint geelhoed_webshop_order_geelhoed_hours_id_fk
        foreign key (hourId) references geelhoed_hours (id);
