truncate wp_erpress2_artists;
insert into wp_erpress2_artists(name, website, twitter, facebook) select distinct(artiste), site_web, twitter, facebook from wp_erpress_titres order by artiste asc;
delete from wp_erpress2_artists where name = 'Cheri Bomb';
delete from wp_erpress2_artists where twitter = 'ElanSeeUsSpin';
delete from wp_erpress2_artists where website = 'http://www.jamendo.com/fr/artist/338761/houdini-roadshow';
update wp_erpress2_artists set facebook = 'http://www.facebook.com/tellusrequiem' where website = 'http://tellusrequiem.com';
delete from wp_erpress2_artists where website = 'http://www.myspace.com/tellusrequiem';
delete from wp_erpress2_artists where website = 'http://www.thurkillsvision.com/site/';
select * from wp_erpress2_artists order by name asc;
