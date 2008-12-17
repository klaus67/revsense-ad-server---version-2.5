-- RevSense MySQL updates to add tiered publisher rates
-- and also future geo-targetting
-- 2007-11-30
--
-- Run these SQL queries to update your database
-- from mysqladmin or from the mysql console
--

alter table adrev_ads add column spend_limit decimal(10,2) default 0;
alter table adrev_ads add column geo varchar(255);
alter table adrev_aff_traffic add column spend decimal(10,3) default 0;
alter table adrev_zones add column pub_rates text;
alter table adrev_zones add column pub_only integer default 0;
