alter table registration_orders_tickettypes
    add id int auto_increment PRIMARY KEY first;

alter table registration_orders_tickettypes
    auto_increment = 1;

alter table registration_orders
    add masterclass tinyint(1) default 0 not null after bhv;
