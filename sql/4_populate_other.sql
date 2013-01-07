truncate wp_erpress2_episodes;
insert into wp_erpress2_episodes select id, nom, publication, archive from wp_erpress_episodes;
truncate wp_erpress2_sources;
insert into wp_erpress2_sources select id, nom, site_web from wp_erpress_sources;
update wp_erpress2_albums set source_id = 1, source_link = 'http://www.cyberpr.biz' where source_id = 0;
