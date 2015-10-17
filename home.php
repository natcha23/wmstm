<h1>Welcome To Thaimart Program</h1>

<span class="btUpload" style="display:none"></span><div id="uploadArea"></div>

<script>
$(function(){

	$.uploadBt = function(){
        $('.btUpload').upload({
            upload_url:'upload_action.php',
            multiFileUpload:1,
            filterType:'image',
            label: 'Attech Files',
            javasvriptCallback:function(f){

             	var img_box = $('<div />',{ id:'image_'+f.id, class:'img_thumb' });
             	var img_remove = $('<i />',{ class:'fa fa-times img_remove' });
                var img = $('<img />',{
                    src:window.URL.createObjectURL(f.file)
                }).css({ 'width':'100px', 'height':'100px' });
                var img_temp = $('<input />').attr({ 'type':'hidden', 'name':'temp_id[]', 'value':f.id });

                $(img_remove).click(function(e){
                	$.removeTemp(f.id);
                	$(this).parent().remove();
                })

                $(img_box).append(img_remove);
                $(img_box).append(img);
                $(img_box).append(img_temp);
                $('#uploadArea').append(img_box);
            }
        });
    }
    $.uploadBt();

    $.removeTemp = function(id){
    	$.post('remove_temp.php',{ id:id },function(html){
    		console.log(html);
    	})
    }

});
</script>
