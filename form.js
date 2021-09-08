$(function() {
	var t = $('#carnaval-control');
	t.css('display','inline');
	$('#add-css').hide();
	t.click(function() {
		$('#add-css').show();
		$(this).hide();
		return false;
	});

	$("#active").change(function()
	{
		if (this.checked) {
			$("#new-class,#classes-form").show();
		}
		else {
			$("#new-class,#classes-form").hide();
		}
	});
	
	if (!document.getElementById('#active').checked) {
		$("#new-class,#classes-form").hide();
	}
});

