jQuery(document).ready(function($) {
	$('#erpress2_add_track_artist_id').click(function() {
		var data = {
				action: 'artist_combo_action',
				artist_id: this.value
		};
		$.post(ajaxurl, data, function(response) {
			var o = $.parseJSON(response);
			$('#erpress2_add_track_album_id').empty();
			$('#erpress2_add_track_album_id').append('<option value="" selected="selected"></option>');
			$.each(o, function(key, value) {
				$('#erpress2_add_track_album_id').append('<option value="' + value.id + '">' + value.title + '</option>');
			});
			$('#erpress2_add_track_album_id').removeAttr('disabled');
		});
	});
	
	$('#erpress2_add_track_album_id').click(function() {
		var data = {
				action: 'album_combo_action',
				album_id: this.value
		};
		$.post(ajaxurl, data, function(response) {
			var o = $.parseJSON(response);
			if (o.source_link != "") {
				$('#erpress2_add_track_source_id').val(o.source_id);
				$('#erpress2_add_track_source_link').val(o.source_link);
			}
		});
	})
});
