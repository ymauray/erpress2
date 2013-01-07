truncate wp_erpress2_albums;
insert into wp_erpress2_albums(title, buy_link, year, source_id, source_link, artist_id) select distinct(t.album), t.lien_achat, t.annee, t.id_source, t.lien_source, a.id from wp_erpress_titres t left join wp_erpress2_artists a on a.name = t.artiste where a.name is not null order by artiste asc;
delete from wp_erpress2_albums where title = 'Loosing Faith';
delete from wp_erpress2_albums where buy_link = 'http://itunes.apple.com/fr/album/see-us-spin/id520014263';
delete from wp_erpress2_albums where buy_link = 'http://www.jamendo.com/fr/album/104765';
delete from wp_erpress2_albums where source_link = 'http://www.myspace.com/interria';
select ar.name, al.* from wp_erpress2_albums al, wp_erpress2_artists ar where ar.id = al.artist_id order by artist_id, title;
