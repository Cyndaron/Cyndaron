alter table geelhoed_clubactie_subscriber
    add numSoldTickets int default 0 not null after email;

alter table geelhoed_clubactie_subscriber
    add soldTicketsAreVerified bool default 0 not null after numSoldTickets;

alter table geelhoed_clubactie_subscriber
    add hash varchar(64) default '' not null after soldTicketsAreVerified;
