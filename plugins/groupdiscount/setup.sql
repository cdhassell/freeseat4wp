use $db;

alter table config add column groupdiscount int(11) NOT NULL;
alter table config add column groupdiscount_min int(11) NOT NULL;
