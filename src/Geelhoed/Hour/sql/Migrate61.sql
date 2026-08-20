alter table geelhoed_hours
    add deleted BIT default 0 not null after notes;
