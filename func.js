$(function(){
    $.path=function(myfile){
        if($.isPlainObject(myfile)){
            var options={
                path:"'.$path.'",
                file:"index_action.php",
                programid:"'.$program.'"
            };
            $.extend(options,myfile);
            return myfile?"ajax.php?path="+options.path+"&file="+options.file+"&program="+options.id:path;
        }else{
            var path="'.$path.'";
            return myfile?"ajax.php?path="+path+"&file="+myfile+"&program="+$.program_id():path;
            //return myfile?"'.$path.'"+myfile:"'.$path.'";
        }
    };
    $.fn.upload=function(option){
		/*
		Filter ::
			pdf => *.pdf
			word => *.doc,*.docx
			image => *.jpg,*.jpeg,*.gif,*.png
			all => *.*
			custom => custom:Web:*.php,*.js,*.css:*.php;*.js;*.css
		Var ::
			array('action'=>'upload5',4,5,'r','g')
				Array(
					POST['action'] => upload5
					POST['var'] => Array(
						[0] => 4
						[1] => 5
						[2] => r
						[3] => g
					)
				)
				
		return
		id:uploadID
		name:file.name,
		size:file.size,
		type:file.type
		*/
		var options={
			vars:{},
			upload_url:'',
			type:'*',
			multiFileUpload:0,
			javasvriptCallback:function(){},
			progress:function(){},
			error:function(){},
			maxFileSize:2097152,
			label:'แนบไฟล์'
		};
		var vars=new Array();
		var type='';
		$.extend(options,option);

		var btFiledata = $('<div />',{class:'fileupload fileupload-new'});
		var btnFile = $('<span />',{class:'btn btn-white btn-file'});
		var selectFile = $('<span />',{class:'fileupload-new'}).html(options.label);
		var Filedata = $('<input>',{
			type:'file',name:'Filedata',id:'Filedata',
			multiple:options.multiFileUpload>0?'multiple':false,
			accept:options.type
		});
		$(btnFile).append(selectFile);
		$(btnFile).append(Filedata);
		$(btFiledata).append(btnFile);
		
		var uploadProgress=this.uploadProgress=function(e){
			var percent=Math.ceil((e.loaded/e.total)*100);
			options.progress(percent);
		}
		
		var uploadFinish=function(e,file){ 
            var json = JSON.parse(e.target.response); 
			myfile={
				id:json.file_id,
				name:file.name,
				size:file.size,
				type:file.type,
				file:file
			};
			options.javasvriptCallback(myfile);
		}
		
		/*var uploadError=this.uploadError=function(e){
			console.log('uploadError');
		}*/
		
		var sendFile=function(file){
			var FData=new FormData();
			FData.append('Filedata', file);
			
			/* Because lose 1 character file.name in POST time */
			FData.append('Filename', file.name);
			
			for(var key in options.vars){
				FData.append(key,options.vars[key]);
			}
			var xhr=new XMLHttpRequest();
			xhr.upload.addEventListener('progress',uploadProgress,false);
			xhr.addEventListener('load',function(e){
				uploadFinish(e,file);
			}, false);
			xhr.addEventListener('error',function(e){
				uploadFinish(e,file);
			}, false);
			xhr.open('POST', options.upload_url, true);
			xhr.send(FData);
		}
		
		this.upload=function(){
			var files=this.files;
			for(var i=0;i<files.length;i++){
				sendFile(files[i])
			}
			
			$(Filedata).val(null);
		}
		
		$(Filedata).change(this.upload);
		$(this).html(btFiledata);
	}
    //function loader
   
    $.loader = function(mode){
        if(mode === 'save'){
            $('#saveSuccess').show();
        }else{
            $('#divLoader').show();
        }
    }
    $.unloader = function(mode){
        if(mode === 'save'){
            $('#saveSuccess').delay(1000).hide("slow");
        }else{
            $('#divLoader').hide('slow');
        }
        
    }
    // function  show hide
    $.toggle = function(id){
        $(id).toggle();
    }
    // function check input number
    $.isNumberKey = function (evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode == 110)
            return true;
        else if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
            return false;

        return true;
    }

    $.checkAll = function(chk_all,chkboxname){ 
        if($(chk_all).is(':checked')){ 
            var checkboxs = $('input[name="'+ chkboxname +'"]');
            $(checkboxs).each(function(e){
                if($(this).is(':visible')==true){
                    this.checked = true
                }
            });
        }else{
            var checkboxs = $('input[name="'+ chkboxname +'"]');
            $(checkboxs).each(function(e){
                if($(this).is(':visible')==true){
                    this.checked = false
                }
            });
        }
    }
    /// uploadFile
    $.uploadBt_div = function(bt_upload,div_area) {
        var divProgress = $('<div />',{
            class:'progress progress-striped'
        });
        var divBar = $('<div />',{
            class:'bar'
        }).css({
            'width': '0%'
        });
        $(divProgress).append(divBar);
        $('.'+bt_upload).upload({
            upload_url: 'index_action.php',
            multiFileUpload: 1,
            filterType: 'image',
            vars: {
                'action': 'Supplier_upload',
            },
            progress:function(p){
                $('.'+bt_upload).append(divProgress);
                if(p==100){
                    $(divBar).css({
                        'width': p+'%'
                    }).html('อัฟโหลดไฟล์เสร็จแล้ว กรุณารอสักครู.....');
                }else{
                    $(divBar).css({
                        'width': p+'%'
                    }).html('กำลังอัฟโหลด '+p+'%');
                }
            },
            javasvriptCallback: function(f) {
                $('.progress').remove();
                var imgName = f.file['name'];

                var div_fram = $('<div />', {
                    id: 'temp_' + f.id, 
                    class: 'img', 
                    title: imgName
                });
                var div = $('<div />');
                var div_del = $('<div />', {
                    class: 'del'
                });

                $('#temp_' + f.id).tooltip();

                var span_remove = $('<div />', {
                    class: 'del_red'
                }).html('<i class="icon-remove"></i>')
                .click(function(e) {
                    $.removeTemp(f.id)
                });
                $(div_del).append(span_remove);
                $(div).append(div_del);

                var imgType = ['jpg', 'jpeg', 'gif', 'png'];
                var docType = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf'];
                var mediaType = ['mp3', 'mp4', 'wmv', 'avi'];

                var arr = imgName.split('.');
                var fType = arr[arr.length - 1];
                fType = fType.toLowerCase();
                var imgPath = $.path() + '../../icon/';

                if ($.inArray(fType, imgType) > -1) {
                    var img = $('<img />', {
                        src: window.URL.createObjectURL(f.file)
                    });
                } else if ($.inArray(fType, docType) > -1) {
                    var i = $.inArray(fType, docType);
                    var img = $('<img />', {
                        src: imgPath + 'filetype_' + docType[i] + '.png'
                    });
                } else if ($.inArray(fType, mediaType) > -1) {
                    var img = $('<img />', {
                        src: imgPath + 'filetype_mov.png'
                    });
                } else {
                    var img = $('<img />', {
                        src: imgPath + 'untitle.png'
                    });
                }

                $(div).append(img);

                var input_hidden = $('<input />', {
                    type: 'hidden', 
                    name: bt_upload+'[]', 
                    'id': 'temp_id_' + f.id, 
                    value: f.id
                });
                $(div).append(input_hidden);

                $(div_fram).append(div);
                console.log(div_fram);
                $('#'+div_area).append(div_fram);
            }
        });
    }

});