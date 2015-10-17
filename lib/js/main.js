$(function(){

	var document_url = document.URL;
	var http_host = 'http://'+location.hostname+'/thaimart/';
	var path = document_url.split(http_host);

	$.testJQ = function(){
		$.post(http_host+'test_jq',{ method:'test' },function(html){
			var main_content = $(html).find('#main_content').html();
			console.log(main_content);
		})
	}

	$.checkActiveMenu = function(){
		var li = $('ul#side-menu li');
		$(li).each(function(i,e){
			
			$(e).removeClass('active');
			if($(e).attr('rel')==document_url){
				$(e).parent('ul.nav-second-level').parent('li').addClass('active');
				$(e).parent('ul.nav-second-level').addClass("collapse in");
				$(e).addClass('active');
			}
		})
	}
	$.checkActiveMenu();

})