alter table richlink
    add image varchar(0) default '' not null after previewImage;
alter table richlink
    add showBreadcrumbs boolean default 0 not null after openInNewTab;
