create table categories
(
    id              int auto_increment
        primary key,
    name            varchar(100)                         not null,
    image           varchar(100)                         not null,
    previewImage    varchar(100)                         not null,
    blurb           varchar(400)                         not null,
    viewMode        tinyint(1) default 0                 not null,
    description     text                                 not null,
    showBreadcrumbs tinyint(1) default 0                 not null,
    created         timestamp  default CURRENT_TIMESTAMP not null,
    modified        timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table category_categories
(
    id         int           not null,
    categoryId int           not null,
    priority   int default 0 not null,
    constraint id
        unique (id, categoryId),
    constraint category_categories_ibfk_1
        foreign key (id) references categories (id)
            on update cascade on delete cascade,
    constraint category_categories_ibfk_2
        foreign key (categoryId) references categories (id)
);

create index categoryId
    on category_categories (categoryId);

create table `cursisten-2020`
(
    Id                         int           null,
    geboortedatum              varchar(19)   null,
    `datum gradatie judo`      varchar(19)   null,
    `datum gradatie jiu jitsu` varchar(10)   null,
    `nummer judobond`          int           null,
    voorletters                varchar(8)    null,
    roepnaam                   varchar(13)   null,
    achternaam                 varchar(26)   null,
    adres                      varchar(27)   null,
    postcode                   varchar(7)    null,
    woonplaats                 varchar(23)   null,
    telefoonnummer             int           null,
    `gradatie judo`            decimal(2, 1) null,
    `gradatie jiu jitsu`       varchar(10)   null,
    `m/v`                      varchar(3)    null,
    senior                     int           null,
    les1                       int           null,
    les2                       int           null,
    rekeningnummer             int           null,
    judo                       varchar(14)   null,
    lescode4                   varchar(5)    null,
    lescode5                   varchar(10)   null,
    lescode6                   varchar(10)   null,
    email                      varchar(35)   null,
    `graduatie judo`           varchar(8)    null,
    judo2                      varchar(6)    null,
    `jiu jitsu`                varchar(5)    null,
    `jiu jitsu2`               varchar(5)    null,
    `rekening/JSF`             varchar(22)   null,
    `graduatie jiu jitsu`      varchar(2)    null,
    ibannr                     varchar(27)   null,
    `2 kw 2014`                varchar(3)    null,
    `tussen voegsel`           varchar(11)   null,
    `jbn lid bevelanden`       varchar(7)    null,
    `jbn lid walcheren`        varchar(7)    null,
    `wedstrijd judoka`         varchar(1)    null,
    huisnummer                 varchar(5)    null,
    `2018 controle`            varchar(10)   null,
    `2019 controle`            varchar(9)    null,
    `Wilma ook les`            varchar(5)    null
)
    charset = utf8mb3;

create table friendlyurls
(
    id       int auto_increment
        primary key,
    name     varchar(100)                        not null,
    target   varchar(750)                        not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint naam
        unique (name),
    constraint target
        unique (target)
);

create table gca2020
(
    id        int auto_increment
        primary key,
    Verkoper  varchar(46)          null,
    Loten     int                  null,
    email     varchar(200)         not null,
    isGemaild tinyint(1) default 0 not null
)
    charset = utf8mb3;

create table geelhoed_clubactie_subscriber
(
    id                     int auto_increment
        primary key,
    firstName              varchar(200)                          not null,
    tussenvoegsel          varchar(50)                           not null,
    lastName               varchar(200)                          not null,
    email                  varchar(200)                          not null,
    phone                  varchar(64) default ''                not null,
    numSoldTickets         int         default 0                 not null,
    soldTicketsAreVerified tinyint(1)  default 0                 not null,
    emailSent              tinyint(1)  default 0                 not null,
    hash                   varchar(64) default ''                not null,
    created                datetime    default CURRENT_TIMESTAMP not null,
    modified               datetime    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table geelhoed_contests_classes
(
    id       int auto_increment
        primary key,
    name     varchar(100)                        not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint name
        unique (name)
);

create table geelhoed_departments
(
    id       int auto_increment
        primary key,
    name     varchar(100)                        not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table geelhoed_sports
(
    id        int auto_increment
        primary key,
    name      varchar(100)                        not null,
    juniorFee double                              not null,
    seniorFee double                              not null,
    created   timestamp default CURRENT_TIMESTAMP not null,
    modified  timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table geelhoed_contests
(
    id                         int auto_increment
        primary key,
    name                       varchar(200)                        not null,
    description                text                                not null,
    location                   varchar(200)                        not null,
    sportId                    int                                 not null,
    registrationDeadline       timestamp                           not null,
    registrationChangeDeadline timestamp                           not null,
    price                      double                              not null,
    created                    timestamp default CURRENT_TIMESTAMP not null,
    modified                   timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_contests_ibfk_1
        foreign key (sportId) references geelhoed_sports (id)
);

create index sportId
    on geelhoed_contests (sportId);

create table geelhoed_contests_dates
(
    id        int auto_increment
        primary key,
    contestId int                                 not null,
    start     timestamp                           not null,
    end       timestamp                           not null,
    created   timestamp default CURRENT_TIMESTAMP not null,
    modified  timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_contests_dates_ibfk_1
        foreign key (contestId) references geelhoed_contests (id)
            on update cascade on delete cascade
);

create index contestId
    on geelhoed_contests_dates (contestId);

create table geelhoed_contests_dates_classes
(
    id            int auto_increment
        primary key,
    contestDateId int                                 not null,
    classId       int                                 not null,
    created       timestamp default CURRENT_TIMESTAMP not null,
    modified      timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint contestDateId
        unique (contestDateId, classId),
    constraint geelhoed_contests_dates_classes_ibfk_1
        foreign key (classId) references geelhoed_contests_classes (id)
            on update cascade on delete cascade,
    constraint geelhoed_contests_dates_classes_ibfk_2
        foreign key (contestDateId) references geelhoed_contests_dates (id)
            on update cascade on delete cascade
);

create index classId
    on geelhoed_contests_dates_classes (classId);

create index contestId
    on geelhoed_contests_dates_classes (contestDateId);

create table geelhoed_graduations
(
    id       int auto_increment
        primary key,
    sportId  int                                not null,
    name     varchar(100)                       not null,
    created  datetime default CURRENT_TIMESTAMP not null,
    modified datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_graduations_ibfk_1
        foreign key (sportId) references geelhoed_sports (id)
            on update cascade on delete cascade
);

create table geelhoed_tryout_points
(
    id       int                                 not null
        primary key,
    code     int                                 not null,
    datetime datetime                            null,
    points   int                                 not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table geelhoed_webshop_product
(
    id             int auto_increment
        primary key,
    parentId       int                                  null,
    name           varchar(255)                         not null,
    description    text                                 not null,
    options        json                                 not null,
    euroPrice      double                               null,
    gcaTicketPrice int                                  null,
    visible        tinyint(1) default 1                 not null,
    created        datetime   default CURRENT_TIMESTAMP not null,
    modified       datetime   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_webshop_product_geelhoed_webshop_product_id_fk
        foreign key (parentId) references geelhoed_webshop_product (id)
            on update cascade
);

create index geelhoed_webshop_product_name_index
    on geelhoed_webshop_product (name);

create table locations
(
    id          int auto_increment
        primary key,
    name        varchar(255)                        not null,
    street      varchar(255)                        not null,
    houseNumber varchar(20)                         not null,
    postalCode  varchar(20)                         not null,
    city        varchar(255)                        not null,
    created     timestamp default CURRENT_TIMESTAMP not null,
    modified    timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table geelhoed_hours
(
    id            int auto_increment
        primary key,
    locationId    int                                 not null,
    day           tinyint                             not null,
    description   varchar(255)                        not null,
    `from`        time                                not null,
    until         time                                not null,
    sportId       int                                 not null,
    sportOverride varchar(100)                        not null,
    departmentId  int                                 not null,
    capacity      int       default 0                 not null,
    minAge        int       default 4                 not null,
    maxAge        int                                 null,
    notes         varchar(255)                        not null,
    created       timestamp default CURRENT_TIMESTAMP not null,
    modified      timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_hours_ibfk_1
        foreign key (locationId) references locations (id)
            on update cascade on delete cascade,
    constraint geelhoed_hours_ibfk_2
        foreign key (sportId) references geelhoed_sports (id),
    constraint geelhoed_hours_ibfk_3
        foreign key (departmentId) references geelhoed_departments (id)
);

create index departmentId
    on geelhoed_hours (departmentId);

create index location_id
    on geelhoed_hours (locationId);

create index sportId
    on geelhoed_hours (sportId);

create table geelhoed_reservation
(
    id       int auto_increment
        primary key,
    hourId   int                                 not null,
    date     date                                not null,
    name     varchar(200)                        not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_reservation_ibfk_1
        foreign key (hourId) references geelhoed_hours (id)
            on update cascade on delete cascade
);

create table geelhoed_volunteer_tot
(
    id             int auto_increment
        primary key,
    name           varchar(200)                       not null,
    locationId     int                                null,
    start          datetime                           not null,
    end            datetime                           not null,
    data           json                               not null,
    photoalbumLink varchar(255)                       not null,
    created        datetime default CURRENT_TIMESTAMP not null,
    modified       datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_volunteer_tot_locations_id_fk
        foreign key (locationId) references locations (id)
            on update cascade on delete set null
);

create index geelhoed_volunteer_event_end
    on geelhoed_volunteer_tot (end);

create index geelhoed_volunteer_event_start
    on geelhoed_volunteer_tot (start);

create index geelhoed_volunteer_event_start_end
    on geelhoed_volunteer_tot (start, end);

create table geelhoed_volunteer_tot_participation
(
    id       int auto_increment
        primary key,
    eventId  int                                not null,
    name     varchar(200)                       not null,
    email    varchar(200)                       not null,
    phone    varchar(30)                        not null,
    type     varchar(200)                       not null,
    data     json                               not null,
    comments text                               not null,
    created  datetime default CURRENT_TIMESTAMP not null,
    modified datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_volunteer_event_participation_id_fk
        foreign key (eventId) references geelhoed_volunteer_tot (id)
            on update cascade on delete cascade
);

create index geelhoed_volunteer_event_participation_email
    on geelhoed_volunteer_tot_participation (email);

create index geelhoed_volunteer_event_participation_et
    on geelhoed_volunteer_tot_participation (eventId, type);

create index geelhoed_volunteer_event_participation_eventId
    on geelhoed_volunteer_tot_participation (eventId);

create table geelhoed_webshop_order
(
    id           int auto_increment
        primary key,
    subscriberId int                                                                                   not null,
    hourId       int                                                                                   not null,
    status       enum ('quote', 'pending_ticket_check', 'pending_payment', 'in_progress', 'delivered') not null,
    paymentId    varchar(64) default ''                                                                not null,
    created      datetime    default CURRENT_TIMESTAMP                                                 not null,
    modified     datetime    default CURRENT_TIMESTAMP                                                 null on update CURRENT_TIMESTAMP,
    constraint geelhoed_webshop_order___fk
        foreign key (subscriberId) references geelhoed_clubactie_subscriber (id),
    constraint geelhoed_webshop_order_geelhoed_hours_id_fk
        foreign key (hourId) references geelhoed_hours (id)
);

create table geelhoed_webshop_order_item
(
    id        int auto_increment
        primary key,
    orderId   int                 not null,
    productId int                 not null,
    quantity  int                 not null,
    options   json                not null,
    price     double              not null,
    currency  enum ('EUR', 'LOT') not null,
    constraint geelhoed_webshop_order_item_geelhoed_webshop_order_id_fk
        foreign key (orderId) references geelhoed_webshop_order (id),
    constraint geelhoed_webshop_order_item_geelhoed_webshop_product_id_fk
        foreign key (productId) references geelhoed_webshop_product (id)
);

create table mailforms
(
    id                int auto_increment
        primary key,
    name              varchar(200)                         not null,
    email             varchar(200)                         not null,
    antiSpamAnswer    varchar(200)                         not null,
    sendConfirmation  tinyint(1) default 0                 not null,
    confirmationText  text                                 null,
    created           timestamp  default CURRENT_TIMESTAMP not null,
    modified          timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    stuur_bevestiging tinyint(1) default 0                 not null,
    tekst_bevestiging mediumtext                           null
);

create table menu
(
    id         int auto_increment
        primary key,
    link       varchar(1000)                        not null,
    alias      varchar(100)                         null,
    isDropdown tinyint(1) default 0                 not null,
    isImage    tinyint(1) default 0                 not null,
    priority   int        default 0                 null,
    created    timestamp  default CURRENT_TIMESTAMP not null,
    modified   timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table minecraft_members
(
    id               int                                  not null
        primary key,
    userName         varchar(100)                         not null,
    uuid             char(32)                             null,
    realName         varchar(150)                         not null,
    level            int                                  not null,
    status           varchar(100)                         not null,
    donor            int        default 0                 not null,
    skinUrl          char(103)                            null,
    renderAvatarHair int        default 1                 not null,
    newRenderer      tinyint(1) default 0                 not null,
    created          timestamp  default CURRENT_TIMESTAMP not null,
    modified         timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table minecraft_servers
(
    id         int                                 not null
        primary key,
    name       varchar(255)                        not null,
    hostname   varchar(255)                        not null,
    port       int                                 not null,
    dynmapPort int                                 not null,
    created    timestamp default CURRENT_TIMESTAMP not null,
    modified   timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table newsletter_subscriber
(
    id        int auto_increment
        primary key,
    name      varchar(200)                         not null,
    email     varchar(200)                         not null,
    confirmed tinyint(1) default 0                 null,
    created   timestamp  default CURRENT_TIMESTAMP not null,
    modified  timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint email
        unique (email)
);

create table photoalbum_captions
(
    id       int auto_increment
        primary key,
    hash     varchar(32)                         not null,
    caption  text                                not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table photoalbums
(
    id               int auto_increment
        primary key,
    name             varchar(100)                         not null,
    image            varchar(100)                         not null,
    previewImage     varchar(100)                         not null,
    blurb            varchar(400)                         not null,
    notes            text                                 not null,
    showBreadcrumbs  tinyint(1) default 0                 not null,
    hideFromOverview tinyint(1) default 0                 not null,
    viewMode         tinyint(1) default 0                 not null,
    thumbnailWidth   int        default 270               not null,
    thumbnailHeight  int        default 200               not null,
    created          timestamp  default CURRENT_TIMESTAMP not null,
    modified         timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table photoalbum_categories
(
    id         int           not null,
    categoryId int           not null,
    priority   int default 0 not null,
    constraint id
        unique (id, categoryId),
    constraint photoalbum_categories_ibfk_1
        foreign key (id) references photoalbums (id)
            on update cascade on delete cascade,
    constraint photoalbum_categories_ibfk_2
        foreign key (categoryId) references categories (id)
);

create index categoryId
    on photoalbum_categories (categoryId);

create table registration_events
(
    id                    int auto_increment
        primary key,
    name                  varchar(200)                           not null,
    openForRegistration   tinyint(1)                             not null,
    description           mediumtext                             not null,
    descriptionWhenClosed mediumtext                             not null,
    registrationCost0     double                                 not null,
    registrationCost1     double                                 not null,
    registrationCost2     double                                 not null,
    registrationCost3     double                                 not null,
    lunchCost             double                                 not null,
    maxRegistrations      int          default 250               not null,
    numSeats              int          default 250               not null,
    requireApproval       tinyint(1)                             not null,
    hideRegistrationFee   tinyint(1)                             not null,
    performedPiece        varchar(200) default ''                not null,
    termsAndConditions    mediumtext                             not null,
    created               timestamp    default CURRENT_TIMESTAMP not null,
    modified              timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table registration_orders
(
    id                   int auto_increment
        primary key,
    eventId              int                                    not null,
    lastName             varchar(250)                           not null,
    initials             varchar(50)                            not null,
    registrationGroup    tinyint(1)   default 0                 not null,
    vocalRange           varchar(50)                            not null,
    birthYear            int                                    null,
    email                varchar(250)                           not null,
    street               varchar(300)                           not null,
    houseNumber          int          default 0                 not null,
    houseNumberAddition  varchar(10)  default ''                not null,
    postcode             varchar(10)                            not null,
    city                 varchar(200)                           not null,
    isPaid               int          default 0                 not null,
    lunch                tinyint(1)   default 0                 not null,
    lunchType            varchar(200) default ''                not null,
    bhv                  tinyint(1)   default 0                 not null,
    kleinkoor            tinyint(1)   default 0                 not null,
    kleinkoorExplanation varchar(500) default ''                not null,
    participatedBefore   tinyint(1)   default 0                 not null,
    numPosters           int          default 0                 not null,
    currentChoir         varchar(120)                           not null,
    choirPreference      varchar(50)                            not null,
    approvalStatus       tinyint(1)   default 0                 not null,
    phone                varchar(20)                            not null,
    choirExperience      int          default 0                 not null,
    performedBefore      tinyint(1)   default 0                 not null,
    comments             varchar(400)                           not null,
    created              timestamp    default CURRENT_TIMESTAMP not null,
    modified             timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint registration_orders_ibfk_1
        foreign key (eventId) references registration_events (id)
);

create index event_id
    on registration_orders (eventId);

create table registration_tickettypes
(
    id           int auto_increment
        primary key,
    eventId      int                  not null,
    name         varchar(200)         not null,
    price        double               not null,
    discountPer5 tinyint(1) default 0 not null,
    constraint event_id
        unique (eventId, name),
    constraint registration_tickettypes_ibfk_1
        foreign key (eventId) references registration_events (id)
            on update cascade on delete cascade
);

create table registration_orders_tickettypes
(
    orderId      int    not null,
    tickettypeId int    not null,
    amount       int(2) not null,
    constraint bestelling_id
        unique (orderId, tickettypeId),
    constraint `bestellings-id_1`
        foreign key (orderId) references registration_orders (id)
            on update cascade on delete cascade,
    constraint `kaartsoort-id_1`
        foreign key (tickettypeId) references registration_tickettypes (id)
            on update cascade on delete cascade
);

create index bestelling_id_2
    on registration_orders_tickettypes (orderId);

create index `kaartsoort-id`
    on registration_orders_tickettypes (tickettypeId);

create index event_id_2
    on registration_tickettypes (eventId);

create table registrationsbk_events
(
    id                    int auto_increment
        primary key,
    name                  varchar(200)                        not null,
    openForRegistration   tinyint(1)                          not null,
    description           mediumtext                          not null,
    descriptionWhenClosed mediumtext                          not null,
    registrationCost      double                              not null,
    performedPiece        varchar(200)                        null,
    termsAndConditions    mediumtext                          not null,
    created               timestamp default CURRENT_TIMESTAMP not null,
    modified              timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table registrationsbk_registrations
(
    id              int auto_increment
        primary key,
    eventId         int                                  not null,
    lastName        varchar(250)                         not null,
    initials        varchar(20)                          not null,
    vocalRange      varchar(50)                          not null,
    email           varchar(250)                         not null,
    phone           varchar(20)                          not null,
    city            varchar(200)                         not null,
    currentChoir    varchar(100)                         not null,
    choirExperience int        default 0                 not null,
    performedBefore tinyint(1) default 0                 not null,
    approvalStatus  tinyint(1) default 0                 not null,
    isPaid          tinyint(1) default 0                 not null,
    comments        varchar(400)                         not null,
    created         timestamp  default CURRENT_TIMESTAMP not null,
    modified        timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint registrationsbk_registrations_ibfk_1
        foreign key (eventId) references registrationsbk_events (id)
);

create index event_id
    on registrationsbk_registrations (eventId);

create table richlink
(
    id              int auto_increment
        primary key,
    name            varchar(100)                         not null,
    previewImage    varchar(100)                         not null,
    image           varchar(0) default ''                not null,
    blurb           varchar(400)                         not null,
    url             varchar(255)                         not null,
    openInNewTab    tinyint(1)                           not null,
    showBreadcrumbs tinyint(1) default 0                 not null,
    created         timestamp  default CURRENT_TIMESTAMP not null,
    modified        timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table richlink_category
(
    id         int           not null,
    categoryId int           not null,
    priority   int default 0 not null,
    constraint id
        unique (id, categoryId),
    constraint richlink_category_ibfk_1
        foreign key (id) references richlink (id)
            on update cascade on delete cascade,
    constraint richlink_category_ibfk_2
        foreign key (categoryId) references categories (id)
);

create index categoryId
    on richlink_category (categoryId);

create table settings
(
    name     varchar(50)                         not null
        primary key,
    value    varchar(1000)                       not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table subs
(
    id              int auto_increment
        primary key,
    name            varchar(100)                           not null,
    image           varchar(100)                           not null,
    previewImage    varchar(100)                           not null,
    blurb           varchar(400)                           not null,
    text            mediumtext                             not null,
    enableComments  int          default 0                 not null,
    showBreadcrumbs tinyint(1)   default 0                 not null,
    tags            varchar(750) default ''                not null,
    created         timestamp    default CURRENT_TIMESTAMP not null,
    modified        timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
);

create table sub_backups
(
    id   int          not null
        primary key,
    name varchar(100) not null,
    text mediumtext   not null,
    constraint sub_backups_ibfk_1
        foreign key (id) references subs (id)
            on update cascade on delete cascade
);

create table sub_categories
(
    id         int           not null,
    categoryId int           not null,
    priority   int default 0 not null,
    constraint id
        unique (id, categoryId),
    constraint sub_categories_ibfk_1
        foreign key (id) references subs (id)
            on update cascade on delete cascade,
    constraint sub_categories_ibfk_2
        foreign key (categoryId) references categories (id)
);

create index categoryId
    on sub_categories (categoryId);

create table sub_replies
(
    id       int auto_increment
        primary key,
    subId    int                                 not null,
    author   varchar(100)                        not null,
    text     mediumtext                          not null,
    created  timestamp default CURRENT_TIMESTAMP not null,
    modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint sub_replies_ibfk_1
        foreign key (subId) references subs (id)
            on update cascade on delete cascade
);

create index subid
    on sub_replies (subId);

create index tags
    on subs (tags);

create table ticketsale_concerts
(
    id                      int auto_increment
        primary key,
    name                    varchar(200)                           not null,
    openForSales            tinyint(1)                             not null,
    description             mediumtext                             not null,
    descriptionWhenClosed   mediumtext                             not null,
    deliveryCost            double                                 not null,
    hasReservedSeats        tinyint(1)   default 0                 not null,
    reservedSeatCharge      double       default 0                 not null,
    forcedDelivery          tinyint(1)   default 0                 not null,
    digitalDelivery         tinyint(1)   default 0                 not null,
    reservedSeatsAreSoldOut tinyint(1)   default 0                 not null,
    numFreeSeats            int          default 250               not null,
    numReservedSeats        int          default 270               not null,
    deliveryCostInterface   varchar(255) default ''                not null,
    secretCode              varchar(32)                            not null,
    date                    timestamp                              not null,
    ticketInfo              text                                   not null,
    locationId              int                                    not null,
    created                 timestamp    default CURRENT_TIMESTAMP not null,
    modified                timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint TC_SC_UNIQUE
        unique (secretCode),
    constraint ticketsale_concerts_locations_id_fk
        foreign key (locationId) references locations (id)
);

create table ticketsale_orders
(
    id                  int auto_increment
        primary key,
    concertId           int                                   not null,
    lastName            varchar(250)                          not null,
    initials            varchar(20)                           not null,
    email               varchar(250)                          not null,
    street              varchar(300)                          not null,
    houseNumber         int         default 0                 not null,
    houseNumberAddition varchar(10) default ''                not null,
    postcode            varchar(10)                           not null,
    city                varchar(200)                          not null,
    delivery            int         default 0                 not null,
    isDelivered         int         default 0                 not null,
    hasReservedSeats    tinyint(1)  default 0                 not null,
    isPaid              int         default 0                 not null,
    deliveryByMember    tinyint(1)  default 0                 not null,
    deliveryMemberName  varchar(300)                          null,
    addressIsAbroad     int         default 0                 not null,
    transactionCode     varchar(32)                           null,
    secretCode          varchar(32)                           null,
    comments            text                                  not null,
    additionalData      text                                  not null,
    created             timestamp   default CURRENT_TIMESTAMP not null,
    modified            timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint secretCode
        unique (secretCode),
    constraint transactionCode
        unique (transactionCode),
    constraint ticketsale_orders_ibfk_1
        foreign key (concertId) references ticketsale_concerts (id)
);

create index concert_id
    on ticketsale_orders (concertId);

create table ticketsale_reservedseats
(
    id        int auto_increment
        primary key,
    orderId   int    not null,
    `row`     char   not null,
    firstSeat int(3) not null,
    lastSeat  int(3) not null,
    constraint ticketsale_reservedseats_ibfk_1
        foreign key (orderId) references ticketsale_orders (id)
            on update cascade on delete cascade
);

create index bestelling_id
    on ticketsale_reservedseats (orderId);

create table ticketsale_tickettypes
(
    id        int auto_increment
        primary key,
    concertId int                                 not null,
    name      varchar(200)                        not null,
    price     double                              not null,
    created   timestamp default CURRENT_TIMESTAMP not null,
    modified  timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint concert_id
        unique (concertId, name),
    constraint ticketsale_tickettypes_ibfk_1
        foreign key (concertId) references ticketsale_concerts (id)
            on update cascade on delete cascade
);

create table ticketsale_orders_tickettypes
(
    id             int auto_increment
        primary key,
    orderId        int                  not null,
    tickettypeId   int                  not null,
    amount         int                  not null,
    secretCode     varchar(32)          null,
    hasBeenScanned tinyint(1) default 0 not null,
    constraint TOTT_SC_UNIQUE
        unique (secretCode),
    constraint `bestellings-id`
        foreign key (orderId) references ticketsale_orders (id)
            on update cascade on delete cascade,
    constraint `kaartsoort-id`
        foreign key (tickettypeId) references ticketsale_tickettypes (id)
            on update cascade on delete cascade
);

create index bestelling_id_2
    on ticketsale_orders_tickettypes (orderId);

create index concert_id_2
    on ticketsale_tickettypes (concertId);

create table users
(
    id                  int auto_increment
        primary key,
    username            varchar(100)                           not null,
    password            varchar(255)                           not null,
    email               varchar(255)                           null,
    level               int                                    not null,
    firstName           varchar(100) default ''                not null,
    initials            varchar(20)                            not null,
    tussenvoegsel       varchar(50)  default ''                not null,
    lastName            varchar(200) default ''                not null,
    role                varchar(100) default ''                not null,
    comments            varchar(500) default ''                not null,
    avatar              varchar(250) default ''                not null,
    hideFromMemberList  tinyint(1)   default 0                 not null,
    gender              enum ('male', 'female', 'other')       null,
    street              varchar(200)                           null,
    houseNumber         int                                    null,
    houseNumberAddition varchar(20)                            null,
    postalCode          varchar(20)                            null,
    city                varchar(200)                           null,
    dateOfBirth         date                                   null,
    optOut              tinyint(1)   default 0                 not null,
    notes               text                                   not null,
    created             timestamp    default CURRENT_TIMESTAMP not null,
    modified            timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint email
        unique (email),
    constraint username
        unique (username)
);

create table geelhoed_members
(
    id                 int auto_increment
        primary key,
    userId             int                                                                       not null,
    parentEmail        varchar(255)                                                              not null,
    phoneNumbers       varchar(255)                                                              not null,
    isContestant       tinyint(1)                                                                not null,
    paymentMethod      enum ('incasso', 'jsf', 'rekening', 'leergeld') default 'incasso'         not null,
    iban               varchar(30)                                     default ''                not null,
    ibanHolder         varchar(200)                                    default ''                not null,
    paymentProblem     tinyint(1)                                      default 0                 not null,
    paymentProblemNote varchar(200)                                                              not null,
    freeParticipation  tinyint(1)                                      default 0                 not null,
    discount           double                                          default 0                 not null,
    temporaryStop      tinyint(1)                                      default 0                 not null,
    joinedAt           date                                                                      null,
    jbnNumber          varchar(30)                                                               not null,
    jbnNumberLocation  varchar(30)                                                               not null,
    created            timestamp                                       default CURRENT_TIMESTAMP not null,
    modified           timestamp                                       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint geelhoed_members_ibfk_1
        foreign key (userId) references users (id)
            on update cascade
);

create table geelhoed_contests_members
(
    id              int auto_increment
        primary key,
    contestId       int                                  not null,
    memberId        int                                  not null,
    graduationId    int                                  not null,
    weight          int                                  not null,
    molliePaymentId varchar(100)                         null,
    isPaid          tinyint(1) default 0                 not null,
    comments        varchar(200)                         not null,
    created         timestamp  default CURRENT_TIMESTAMP not null,
    modified        timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint contestId
        unique (contestId, memberId),
    constraint geelhoed_contests_members_ibfk_1
        foreign key (contestId) references geelhoed_contests (id)
            on update cascade on delete cascade,
    constraint geelhoed_contests_members_ibfk_2
        foreign key (memberId) references geelhoed_members (id)
            on update cascade on delete cascade,
    constraint geelhoed_contests_members_ibfk_3
        foreign key (graduationId) references geelhoed_graduations (id)
);

create index graduationId
    on geelhoed_contests_members (graduationId);

create index memberId
    on geelhoed_contests_members (memberId);

create table geelhoed_members_graduations
(
    id           int auto_increment
        primary key,
    memberId     int  not null,
    graduationId int  not null,
    date         date null,
    constraint geelhoed_members_graduations_ibfk_1
        foreign key (memberId) references geelhoed_members (id)
            on update cascade on delete cascade,
    constraint geelhoed_members_graduations_ibfk_2
        foreign key (graduationId) references geelhoed_graduations (id)
            on update cascade on delete cascade
);

create index graduationId
    on geelhoed_members_graduations (graduationId);

create index memberId
    on geelhoed_members_graduations (memberId);

create table geelhoed_members_hours
(
    memberId int not null,
    hourId   int not null,
    constraint geelhoed_members_hours_ibfk_1
        foreign key (hourId) references geelhoed_hours (id)
            on update cascade on delete cascade,
    constraint geelhoed_members_hours_ibfk_2
        foreign key (memberId) references geelhoed_members (id)
            on update cascade on delete cascade
);

create index hour_id
    on geelhoed_members_hours (hourId);

create index member_id
    on geelhoed_members_hours (memberId);

create table geelhoed_users_members
(
    userId   int                     not null,
    memberId int                     not null,
    relation varchar(100) default '' not null,
    constraint userId
        unique (userId, memberId),
    constraint geelhoed_users_members_ibfk_1
        foreign key (memberId) references geelhoed_members (id),
    constraint geelhoed_users_members_ibfk_2
        foreign key (userId) references users (id)
);

create index memberId
    on geelhoed_users_members (memberId);

create table user_rights
(
    userId  int         not null,
    `right` varchar(63) not null,
    constraint userId
        unique (userId, `right`),
    constraint user_rights_ibfk_1
        foreign key (userId) references users (id)
            on update cascade on delete cascade
);

