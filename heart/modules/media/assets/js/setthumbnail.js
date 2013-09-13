$(document).ready(function(){

	$('#folder_id').chosen({'width': '100%'});

	$('.m-thumbs').bind('click', function(){

		if (! $(this).hasClass('m-thumb-active')) {
			$('.m-thumbs').removeClass('m-thumb-active');
			$(this).addClass('m-thumb-active');
		}

		$('#width').val($(this).attr('data-width'));
		$('#height').val($(this).attr('data-height'));
		$('#alt_text').val($(this).attr('data-alt'));

		var image = "<img src='"+SITEURL+"media/image/"+$(this).attr('data-filename')+"/300/200'>";

		var imageName = "<p>"+$(this).attr('data-name')+"</p>";

		var imageUrl = "<input type='hidden' value='"+$(this).attr('data-filename')+"' id='image_url'>";

		$('#m-thumb-preview-wrap').html(image+imageName+imageUrl);

		$('#m-thumb-preview-wrap').show();
	});

	$('#folder_id').chosen().change(function(){
		$('div#media_body').load(ADMIN + '/media/feature-image/' + $(this).val() + ' #ajax_wrap');
	});
});

function insert()
{
	var target = window.parent.document;
	console.log(target);
	var targetImg = $('.thumbnail_preview', target),
		targetInput = target.getElementById(THUMB_TARGET),
		targetRmBtn = $('.thumbnail_remove_btn', target);
		targetAddBtn = $('.thumbnail_add_btn', target);

	var width = ($('#width').val()) ? $('#width').val() : 0,
		height = ($('#height').val()) ? $('#height').val() : 0,
		alt = $('#alt_text').val();

	var img = "<img class='thumbnail_image' src='"+SITEURL+'media/image/'+$('#image_url').val() +"/"+ THUMB_WIDTH + "' alt='"+alt+"'>";
	var value = $('#image_url').val()+"/"+width+"/"+height+"/";

	$('.thumbnail_image', target).remove();
	$(targetAddBtn).hide();
	$(targetRmBtn).show();

	$(targetImg).append(img);
	$(targetInput).val(value);

	$.colorbox.close();
}

/*function removeFeature()
{
	$(document.getElementById('featured_remove_btn')).hide();
	$(document.getElementById('featured_add_btn')).show();

	$('#f_image_wrap img').remove();
}*/

function imgPreview(id, name, alt, width, height)
{
	$('#m_preview_area').show('fast');

	var src = SITEURL + "media/image/"+id+"/185/";
	var current = $('#img_set_preview img').attr('src');
	var hiddenField = "<input type='hidden' id='image_url' value='"+id+"/'>";

	if (current) {
		if (src != current) {
			$('#img_set_preview img').remove();
			$('#img_set_preview #image_url').remove();
			$('#img_set_preview p').remove();
		} else { return; }
	}

	$('#img_set_preview').append("<img src='"+src+"'>");
	$('#img_set_preview').append('<p>'+name+'</p>');
	$('#img_set_preview').append(hiddenField);
	$('#width').val(width);
	$('#height').val(height);
	$('#fileId').val(id);
}