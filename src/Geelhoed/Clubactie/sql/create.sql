create table if not exists geelhoed_clubactie_subscriber
(
    id            int auto_increment PRIMARY KEY,
    firstName     varchar(200)                       not null,
    tussenvoegsel varchar(50)                        not null,
    lastName      varchar(200)                       not null,
    email         varchar(200)                       not null,
    created       datetime default current_timestamp not null,
    modified      datetime default current_timestamp not null on update current_timestamp
);

