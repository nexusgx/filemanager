var lightbox = {
    init: function() {
        $('.lightbox-close').on('click', this.hide);
        $('#fade').on('click', this.hide);
    },
    show: function(header) {
        if(typeof header==='string'){
            $('.lightbox-header').html(header);
        }
        else{
            $('.lightbox-header').html('');
            $('.lightbox-header').hide();
        }
        $('.lightbox').fadeIn();
        $('#fade').fadeIn();
    },
    hide: function() {
        $('.lightbox').hide();
        $('#fade').hide();
    }
};
var prompt = {
    is_alert:false,
    is_init:false,
    show: function(question,ok_obj,cancel_obj) {
        var ok={},cancel={};
        if(typeof question==='string'){
            $('#prompt .text').html(question);
        }
        else{
            return;
        }
        if(!prompt.is_init){
            $('.input').keypress(function (e) {
                if (e.which == 13) {
                    $('#prompt [data-func="ok"]').trigger('click');
                }
            });
        }
        ok=$.extend({text:'OK','action':this.hide},ok_obj);
        cancel=$.extend({text:'Cancel','action':this.hide},cancel_obj);
        $('#prompt [data-func="ok"]').text(ok.text);
        $('#prompt [data-func="cancel"]').text(cancel.text);
        $('#prompt [data-func="ok"]').off('click').on('click',function(){
            ok.action($('#prompt .input input').val());
            prompt.hide();
        });
        $('#prompt [data-func="cancel"]').off('click').on('click',cancel.action);
        
        if(prompt.is_alert)
            $('#prompt .input').hide();
        else
            $('#prompt .input').show();
        
        $('#prompt').slideDown();
    },
    hide: function() {
        prompt.is_alert=false;
        $('#prompt').slideUp(200);
        $('#prompt .input input').val('');
    },
    alert:function(question,ok_obj,cancel_obj){
        prompt.is_alert=true;
        prompt.show(question,ok_obj,cancel_obj);
    },
    set_value:function(value){
        $('#prompt .input input').val(value);
    }
};

var manager={
    dir:'',
    files:{},
    nav:{},
    error:'',
    get_dir:function(f,func){
        if(typeof f==='undefined') 
            f='/';
        this.dir=f;
        data={action:'get_dir',dir:this.dir};
        $.post( "index.php",data, function(ret) {
            try{
                manager.files=JSON.parse(ret);
                if(typeof func!=='undefined')
                    func(manager.files);
            }
            catch(e){
                return false;
            }
        });
    },
    get_breadcrumbs:function(func){
        data={action:'get_nav',dir:this.dir};
        $.post( "index.php",data, function(ret) {
            try{
                manager.nav=JSON.parse(ret);
                if(typeof func!=='undefined')
                    func(manager.nav);
            }
            catch(e){
                return false;
            }
        });
    },
    get_file:function(file,func){
        data={action:'check_file','dir':this.dir,file:manager.files[file].filename};
        $.post( "index.php",data, function(ret) {
            console.log(ret);
            try{
                if(typeof func!=='undefined')
                    func(JSON.parse(ret));   
            }
            catch(e){
                return false;
            }
        });
    },
    new_dir:function(name,func){
        for(var i=0;i<this.files.length;i++){
            if(this.files[i].filename===name){
                this.error='This folder already exists';
                return false;
            }
        }
        data={action:'new_dir','dir':this.dir,name:name};
        $.post( "index.php",data, function(ret) {
            try{
                if(typeof func!=='undefined')
                    func(JSON.parse(ret));
            }
            catch(e){
                return false;
            }
        });
    },
    new_file:function(name,func){
        for(var i=0;i<this.files.length;i++){
            if(this.files[i].filename===name){
                this.error='This file already exists';
                return false;
            }
        }
        data={action:'new_file','dir':this.dir,name:name};
        $.post( "index.php",data, function(ret) {
                console.log(ret);
            try{
                if(typeof func!=='undefined')
                    func(JSON.parse(ret));
            }
            catch(e){
                return false;
            }
        });
    },
    rename:function(file,name,func){
        for(var i=0;i<this.files.length;i++){
            if(this.files[i].filename===name){
                this.error='This file already exists';
                return false;
            }
        }
        data={action:'rename','dir':this.dir,'file':manager.files[file].filename,name:name};
        $.post( "index.php",data, function(ret) {
                console.log(ret);
            try{
                manager.files[file].filename=name;
                if(typeof func!=='undefined')
                    func(JSON.parse(ret));
            }
            catch(e){
                return false;
            }
        });
    },
    delete:function(file,func){
        var files=[];
        if(typeof file==='undefined') return;
        if(file.length===0) return;
        
        for(var i=0;i<file.length;i++){
            files.push(manager.files[file[i]].filename);
        }
        data={action:'delete','dir':this.dir,'file':files};
        $.post( "index.php",data, function(ret) {
                console.log(ret);
            try{
                delete manager.files[file];
                if(typeof func!=='undefined')
                    func(JSON.parse(ret));
            }
            catch(e){
                return false;
            }
        });
    },
    update_file:function(file,opts){
        if(typeof opts==='undefined')
            opts={};
        this.files[file]=$.extend(this.files[file],opts);
        
    }
};

var global={
    last_selected:999,
    total_selected:0,
    get_query:function() {
        if(typeof this.querystring==='undefined'){
            var pairs = location.search.slice(1).split('&');

            var result = {};
            pairs.forEach(function(pair) {
                pair = pair.split('=');
                result[pair[0]] = decodeURIComponent(pair[1] || '');
            });

            this.querystring=JSON.parse(JSON.stringify(result));
        }
        return this.querystring;
    }
};
function error(str){
    console.log(str);
}


/*

SET UP A WAY TO REBUILD THE FILES ARRAY WITHOUT PULLING IT FROM THE DB
CHANGE VALUES IN THE FILES ARRAY 

*/


function open_dir(dir){
    $('body').addClass('loading');
    manager.get_dir(dir,function(files){
        
        global.total_selected=0;
        $('#edit').hide();
        html='';
        for(var i=0;i<files.length;i++){
            html+='<div data-file="'+files[i].rel_filename+'" class="file '+files[i].type+'"'+
                'title="'+files[i].filename+'" data-i="'+i+'" data-type="'+files[i].type+'">'+
                '<div class="icon"><i class="fa fa-'+files[i].icon+'"></i></div>'+
                '<div class="name">'+files[i].filename+'</div>'+
                '<div class="type">'+files[i].type+'</div>'+
                '<div class="filedate" data-date="'+files[i].modified_timestamp+'">'+files[i].date+'</div>'+
                '<div class="filesize" data-size="'+files[i].raw_size+'">'+files[i].filesize+'</div>'+
                '</div>';
        }
        $('.file').remove();
        $('#files').html(html);
        $('#info').text(files.length+' file(s)');

        //remove selection
        $('#files').off('click').on('click',function(event) { 
            if(!$(event.target).closest('.file').length) {
                $('.selected').removeClass('selected');
                $('#info').text(files.length+' file(s)');
                $('#edit').hide();
            }        
        })

        //select files
        $('.file').on('click',function(e){
            var index=parseInt($(this).data('i')),
                text='';
            
            if(!e.ctrlKey){
                if($(this).hasClass('selected')) return; //kill if already selected
                $('.selected').removeClass('selected');
            }
            if(e.shiftKey){ //select multiples

                if(index > global.last_selected){
                    while(index >= global.last_selected){
                        $('.file[data-i="'+index+'"]').addClass('selected');
                        index--;
                    }
                }
                else{
                    while(index <= global.last_selected){
                        $('.file[data-i="'+index+'"]').addClass('selected');
                        index++;
                    }
                }
                $(this).addClass('selected');
                global.total_selected=$('.selected').length;

                text=global.total_selected+' files selected';
            }
            else if(e.ctrlKey){
                if($(this).hasClass('selected'))
                    $(this).removeClass('selected');
                else
                    $(this).addClass('selected');
                global.total_selected=$('.selected').length;
                text=global.total_selected+' files selected';
            }
            else{ //deselect everything except this one
                global.last_selected=index;
                $(this).addClass('selected');
                global.total_selected=1;
                console.log(manager.files[index]);
                text=manager.files[index].rel_filename;
                if(manager.files[index].type!=='dir')
                    text+=' - Size:'+manager.files[index].filesize;
                text+=' - Modified:'+manager.files[index].modified;
            }
            $('#info').text(text);
            
            if(global.total_selected === 1){
                $('#edit .single').show();
            }
            else{
                $('#edit .single').hide();
            }
            if(global.total_selected > 0){
                $('#edit').show();
            }
            else{
                $('#edit').hide();
            }
        });
        
        //open the file
        $('.file[data-type!="dir"]').on('dblclick',function(){
            var index=$(this).data('i');
            if(typeof index==='undefined')return;
            var file=manager.files[index];
            $('#image').html('');
            $('#text textarea').html('');
            manager.get_file(index,function(file){
                if(!file){
                    error('This type of file is not permitted.');
                }
                else{
                        console.log(file);
                    $('#image').hide();
                    $('#text').hide();
                    $('#iframe').hide();
                    if(file.action=='view'){
                        if(file.type==='jpg' || file.type==='gif' || file.type==='png'){
                            $('#image').html('<img src="'+global.base_url+file.rel_filename+'">');
                            $('#image').show();
                            lightbox.show(file.filename);
                        }
                        else{
                            console.log(global.base_url+encodeURIComponent(file.rel_filename));
                            window.open(global.base_url+encodeURIComponent(file.rel_filename));
                            //$('#iframe iframe').attr('src',global.base_url+encodeURIComponent(file.rel_filename));
                        }
                    }
                    else if(file.action=='edit'){
                        $('#text textarea').text(file.contents);
                        $('#text').show();
                        lightbox.show(file.filename);
                    }
                    else{
                        $('#iframe iframe').attr('src','download.php?file='+encodeURIComponent(file.rel_filename));
                    }
                }
            });
            
        });

        $('.dir').on('dblclick',function(){open_dir($(this).data('file'));});

        manager.get_breadcrumbs(function(nav){
            html='';
            for(var i=0;i<nav.length;i++){
                html+='<li><a data-path="'+nav[i].rel_path+'">'+nav[i].name+'</a></li>';
            }
            $('.breadcrumb li').remove();
            $('.breadcrumb').html(html);
            $('.breadcrumb li a').on('click',function(){open_dir($(this).data('path'));});
            $('body').removeClass('loading');
        });
    });
    //window.location.href=window.location.origin+window.location.pathname+'?dir='+$(this).data('file');
}

function search(term){
    if(typeof term==='undefined'){ 
        $('#searchclear').hide();
        $('.file').show(); 
        return; 
    }
    else if(term===''){ 
        $('#searchclear').hide();
        $('.file').show(); 
        return;
    }
    else{
        $('#searchclear').show();
    }
    
    var selector='';
    $('.file').hide();
    for(var i=0;i<manager.files.length;i++){
        if(manager.files[i].filename.search(term)!==-1){
            selector+='.file[data-i="'+i+'"],';
        }
    }
    $(selector.slice(0,-1)).show();
}

$(document).ready(function(){
    global.base_url=base_url;
    dir=global.get_query().dir;
    delete base_url;
    
    $('#view button').on('click',function(){
        var cls=$(this).data('view');
        $('#files').attr('class',cls);
        localStorage.view=cls;
    });
    $('#new_dir').on('click',function(){
        prompt.show('Enter the name you want for your new directory.',{action:function(name){
            console.log(name);
            if(!manager.new_dir(name,function(){
                open_dir(manager.dir);
            }))
                error(manager.error);
        }});
    });
    $('#new_file').on('click',function(){
        prompt.show('Enter the name you want for your new file.',{action:function(name){
            if(!manager.new_file(name,function(){
                open_dir(manager.dir);
            }))
                error(manager.error);
        }});
    });
    $('#edit button[data-action="rename"]').on('click',function(){
        if(global.total_selected===1){
            prompt.set_value(manager.files[$('.selected').data('i')].filename);
            prompt.show('Enter the new name you want for your file.',{action:function(name){
                if(!manager.rename($('.selected').data('i'),name,function(){
                    console.log('renamed');
                    $('.file[data-i="'+$('.selected').data('i')+'"] .name').text(name);
                    //open_dir(manager.dir);
                }))
                    error(manager.error);
            }});
        }
    });
    $('#edit button[data-action="delete"]').on('click',function(){
        prompt.set_value(manager.files[$('.selected').data('i')].filename);
        prompt.alert('Are you sure you want to delete these files? All files within selected folders will also be deleted.',{action:function(){
            var files=$( ".selected" ).map(function() {return $(this).data('i');}).get();
            if(!manager.delete(files,function(){
                console.log('deleted');
                open_dir(manager.dir);
            }))
                error(manager.error);
        }});
    });
    $('#upload').on('click',function(){
        console.log('click');
        $('#file_upload input[type="file"]').trigger('click');
    });
    $('#file_upload input[type="file"]').on('change',function(){
        $('#file_upload input[name="dir"]').val(manager.dir);
        $('#file_upload form').submit();
    });
    $('#file_upload form').on('submit',function(e){
        $('#iframe_up').on('load',function(){
            console.log($('#iframe_up').contents()[0].body.innerHTML);
            $('#file_upload input[type="file"]').val('');
            $('#iframe_up').off('load');
        });
    });
    $('#search input[type="text"]').on('keyup',function(){
        search(this.value);
    });
    $('#search button').on('click',function(){
        search($('#search input[type="text"]').val());
    });
    $('#searchclear').click(function(){
        $('#search input[type="text"]').val('');
        search();
    });
    
    if(typeof localStorage.view!=='undefined'){
        $('#files').attr('class',localStorage.view);
    }
    open_dir(dir);
    lightbox.init();
});