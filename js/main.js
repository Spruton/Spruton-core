var is_mobile = navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i);
var app_choices_values = new Array();
var smartystreets = false;
var is_resizable_process = false;

function app_get_choices_values(id)
{		
	if($.isArray(id))
	{
		sum = 0;
					
		for(i=0;i<id.length;i++)
		{
			if(typeof app_choices_values[id[i]] != 'undefined') 
			{
				sum = sum+app_choices_values[id[i]];
			}						
		}
		
		return sum;
	}
	else
	{					
		if(typeof app_choices_values[id] === 'undefined') 
		{
		  return 0;
		}
		else 
		{
		  return app_choices_values[id];
		}		
	}
}

function validate_user_form(form,url)
{    
  $.ajax({
    type: "POST",
    url: url,
    data: { username: $('#fields_12').val(), useremail: $('#fields_9').val() }
  })
  .done(function( msg ) {
      msg = msg.trim()      
      if(msg=='success')
      {
        form.submit();
      }
      else
      {
        $("div#form-error-container").html('<div class="note note-danger">'+msg+'</div>');
  			$("div#form-error-container").show();
        $("div#form-error-container").delay(5000).fadeOut();
        
        $('.btn-primary-modal-action').show();
        $('.primary-modal-action-loading').css('visibility','hidden');
        $('.btn-primary-modal-action',form).prop('disabled',false);
      }
  });      
}

function app_prepare_modal_action_loading(obj)
{
  $('.btn-primary-modal-action',obj).hide();
  $('.primary-modal-action-loading',obj).css('visibility','visible');
}

function app_highlight_form_tab_name_with_errors(form_id)
{
  //highlight tab name with errors          	                  
  setTimeout(function() {
     
     var is_active_tab = false;
     
     $('#'+form_id+' .tab-pane').each(function(){
        
        var has_error = false;
        
        tab_id = $(this).attr('id')  
        $('#'+tab_id+' .error:not(label)').each(function(){
          has_error = true                                          
        })
        
        if(has_error)
        {                        
          $("a[href='#"+tab_id+"']").addClass('error');
          
          //atuomaticaly open firts tab with error
          if(is_active_tab==false)
          {
            $('#'+form_id+' .nav-tabs>li').removeClass('active')
            $('#'+form_id+' .tab-pane').removeClass('active').removeClass('in')
            
            $('#'+form_id+' .nav-tabs>li.'+tab_id).addClass('active')
            $('#'+form_id+' #'+tab_id).addClass('active').addClass('in')
            
            is_active_tab = true;
          }
        }
        
     })             
  }, 50);
  
  //remove highlight
  setTimeout(function() {
    $('#'+form_id+' .nav-tabs>li>a').removeClass('error');
  }, 5000);
}

function use_editor(id,is_focus,height)
{    
  if(!height)
  {
		height=150;	
  }
  
  
	if(!$('#'+id).hasClass('ckeditorInstanceReady'))
	{
		$('#'+id).addClass('ckeditorInstanceReady')
		
	  CKEDITOR.config.baseFloatZIndex = 20000;
	  CKEDITOR.config.height = height;
	  CKEDITOR_holders[id] = CKEDITOR.replace(id,{startupFocus:is_focus,language: app_language_short_code, toolbar: (app_language_text_direction=='rtl' ? 'Rtl':'Default')});//
	
	  CKEDITOR_holders[id].on("instanceReady",function() {
	    jQuery(window).resize();
	
	    $(".cke_button__maximize").bind('click', function() {
	    	$('#ajax-modal').css('display','block')
	    })
	  });
	}
     
} 

function use_editor_full(id,is_focus, height)
{
	if(!height)
  {
  	height=450;
  }
  
	if(!$('#'+id).hasClass('ckeditorInstanceReady'))
	{
		$('#'+id).addClass('ckeditorInstanceReady')
		
		CKEDITOR.config.baseFloatZIndex = 20000;
		
	  CKEDITOR_holders[id] = CKEDITOR.replace(id,{height:height, startupFocus:is_focus,language: app_language_short_code,toolbar: (app_language_text_direction=='rtl' ? 'RtlFull':'Full')});
	  
	  CKEDITOR_holders[id].on("instanceReady",function() {
	    jQuery(window).resize();
	
	    $(".cke_button__maximize").bind('click', function() {
	    	$('#ajax-modal').css('display','block')
	    })
	  });
	}
} 

function spruton_app_init()
{
	
  $('.datepicker').datepicker({
            rtl: App.isRTL(),
            autoclose: true,
            weekStart: app_cfg_first_day_of_week,
            format: 'yyyy-mm-dd',
            clearBtn: true,
        });
        
 $(".datetimepicker-field").datetimepicker({
        autoclose: true,
        isRTL: App.isRTL(),
        format: "yyyy-mm-dd hh:ii",
        weekStart: app_cfg_first_day_of_week,
        pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left"),
        clearBtn: true,
    });      
      
                     
 $.fn.modal.defaults.spinner = $.fn.modalmanager.defaults.spinner = 
          '<div class="loading-spinner" style="width: 200px; margin-left: -100px;">' +
            '<div class="progress progress-striped active">' +
              '<div class="progress-bar" style="width: 100%;"></div>' +
            '</div>' +
          '</div>';
        
            
  $( "textarea.editor" ).each(function() { use_editor($(this).attr('id'),false) });
  $( "textarea.full-editor" ).each(function() { use_editor_full($(this).attr('id'),false,$(this).attr('editor-height')) });
  
  appHandlePopover();   
  
  appHandleNumberInput();
  
  $('[data-toggle="tooltip"]').tooltip()   
  
  
  $('.chosen-select').each(function(){      
      width = '100%';

      if(!is_mobile)
      {	
      	if($(this).hasClass('input-small')) width = '120px';
      	if($(this).hasClass('input-medium')) width = '240px';
      	if($(this).hasClass('input-large')) width = '320px';
      	if($(this).hasClass('input-xlarge')) width = '480px';
      }
      
      $(this).chosen({width: width,
                      include_group_label_in_selected: true,
                      search_contains: true,
                      no_results_text:i18n['TEXT_NO_RESULTS_MATCH'],
                      placeholder_text_single:i18n['TEXT_SELECT_AN_OPTION'],
                      placeholder_text_multiple:i18n['TEXT_SELECT_SOME_OPTIONS']
                      }).chosenSortable();
   })
            
   $().UItoTop({
   	scrollSpeed:500,
   	easingType:'linear'
   });	
  
	//hightlight code
  hljs.initHighlightingOnLoad();  
  
  hljs_init_copy_code();
  
  appHandleSelectAll();
  
  //prevent double click on button
  $('.prevent-double-click').click(function(){
  	$(this).attr('disabled','disabled')
  })
  
  
	//map fullscreen
  $('.image-map-fullscreen-action').click(function(){
  	if($('.image-map-iframe-box').hasClass('div-fullscreen'))
  	{
  		$('.image-map-iframe-box').removeClass('div-fullscreen')
  		$(this).html('<i class="fa fa-arrows-alt"></i>');
  	}
  	else
  	{
  		$('.image-map-iframe-box').addClass('div-fullscreen')
  		$(this).html('<i class="fa fa-compress"></i>');
  	}
  	
  	resize_image_map_iframe();
  })
  
  //map fullscreen
  $('.mind-map-fullscreen-action').click(function(){
  	field_id = $(this).attr('data_field_id');  	
  	if($('.mind-map-iframe-box-'+field_id).hasClass('div-fullscreen'))
  	{
  		$('.mind-map-iframe-box-'+field_id).removeClass('div-fullscreen')
  		$(this).html('<i class="fa fa-arrows-alt"></i>');
  	}
  	else
  	{
  		$('.mind-map-iframe-box-'+field_id).addClass('div-fullscreen')
  		$(this).html('<i class="fa fa-compress"></i>');
  	}
  	
  	resize_mind_map_iframe_field(field_id)
  	  	
  })
      
} 

function resize_mind_map_iframe_field(field_id)
{	
	if($('.mind-map-iframe-box-'+field_id).hasClass('div-fullscreen'))
	{
		height = $(window).height();		
	}
	else
	{	
		if(field_id==0)
		{
			height = $(window).height()-$('.page-title').height()-$('.portlet-filters-preview').height()-$('.navbar').height()-$('.page-breadcrumb').height()-150;
		}
		else
		{					
	 		height = 450;
		}
	}

	$('.mind-map-iframe-'+field_id).css('height',height) 
}

function resize_image_map_iframe()
{
	if($('.image-map-iframe-box').hasClass('div-fullscreen'))
	{
		height = $(window).height();
	}
	else
	{	
	 	height = $(window).height()-$('.page-title').height()-$('.portlet-filters-preview').height()-150;
	}

	 $('.image-map-iframe').css('height',height) 
}

function hljs_init_copy_code()
{
	
	$('code').each(function() {
		if(!$(this).hasClass('hljs_tools'))
		{	
			var obj = $(this);
    	$(this).append('<div class="hljs_code_tools"><a href="#" onClick="return false" class="btn btn-default btn-xs hljs_code_tools_clipboard"><i class="fa fa-clipboard" aria-hidden="true"></i></div>').addClass('hljs_tools')
    	
    	$(this).on('click', '.hljs_code_tools_clipboard', function() {
				//alert(obj.text())
    		copyToClipboard(obj)
			})
    	    	
		}
	});	
	
	//allowfullscreen for iframe
	$('iframe').attr('allowfullscreen','true')
}

function copyToClipboard(obj) 
{
  var $temp = $("<textarea>");
  obj.append($temp);
  $temp.val(obj.text()).select();
  document.execCommand("copy");
  $temp.remove();
}

function open_dialog(url)
{   
	//open current window if it's collapsed
	if($('.modal-backdrop').hasClass('modal-collapsed'))  
  {
  	$('.modal-backdrop').removeClass('modal-collapsed')
  	$('.modal-scrollable').removeClass('modal-collapsed')
  	
  	jQuery(window).resize();
  	
		return false;
  }	
	
	//start open new window
  var $modal = $('#ajax-modal');
    
  // create the backdrop and wait for next modal to be triggered
  if(!$('body').hasClass('modal-open'))
    $('body').modalmanager('loading');
    
  setTimeout(function(){
      $modal.load(url, '', function(response, status, xhr){
                                                                        
      	
      if($('#ajax-modal .form-control').hasClass('input-xlarge') || $('#ajax-modal textarea').hasClass('editor') || $('#ajax-modal textarea').hasClass('editor-auto-focus') || $('#ajax-modal div').hasClass('ajax-modal-width-790') || $('#ajax-modal button').hasClass('btn-submodal-open'))          
      {        
        width = 790
      }
      else
      {
        width = 590        
      }
      
      if($('#ajax-modal div').hasClass('ajax-modal-width-1100'))
      {
      	width = 1100
      }
                
      $modal.modal({width:width}); 
      
      $("#ajax-modal").draggable({
            handle: ".modal-header,.modal-footer"
        });
                        
      if((response.search('app_db_error')>0 || response.search('Fatal error')>0) && response.search('modal-header')==-1)
      {
        $('#ajax-modal').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button><h4 class="modal-title">Error</h4></div>'+response+'<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>');
      }
                 
      //cancel hander
    	$('[data-dismiss="modal"]').click(function(){
    		
    		//handle cancle gantt
    		if ($('#gantt_delete_item_btn').length) gantt_cancel();
    		
    		//hande smartystreet cancel
    		if(smartystreets) smartystreets.deactivate();
    		
      })            
                                               
    });
  }, 1); 
}

function appHandleUniformInListing()
{	
  var test = $("input[type=checkbox]:not(.toggle), input[type=radio]:not(.toggle, .star)");
  if (test.size() > 0) {
      test.each(function () {
          if ($(this).parents(".checker").size() == 0) {
              $(this).show();
              $(this).uniform();
          }
      });
  }
      
  appHandlePopover();
}  

function appHandlePopover()
{
  $('[data-toggle="popover"]').popover({trigger:'hover',html:true,
     placement: function (context, source) {
        var position = $(source).position();
                        
        if($(source).attr('placement'))
        {
					return $(source).attr('placement');	
        }
        
        //alert(position.left);
        
        if (position.left < 350) {
            return "right";
        }
        
        if (position.left > 350) {
            return "left";
        }
        
        if (position.top < 200){
            return "bottom";
        }
  
        return "top";
    }  
  })
}

function appHandleUniformCheckbox(){
  var test = $("input[type=checkbox]:not(.toggle)");
  if (test.size() > 0) {
      test.each(function () {
          if ($(this).parents(".checker").size() == 0) {
              $(this).show();
              $(this).uniform();
          }
      });
  }
}

function appHandleUniform()
{
  var test = $("input[type=checkbox]:not(.toggle), input[type=radio]:not(.toggle, .star)");
  if (test.size() > 0) {
      test.each(function () {
          if ($(this).parents(".checker").size() == 0) {
              $(this).show();
              $(this).uniform();
          }
      });
  }
  
  
 $('.datepicker').datepicker({
              rtl: App.isRTL(),
              autoclose: true,
              weekStart: app_cfg_first_day_of_week,
              format: 'yyyy-mm-dd',
              clearBtn: true,              
          });

 
 $(".datetimepicker-field").each(function(){
	  		
 		$(this).datetimepicker({
        autoclose: true,
        isRTL: App.isRTL(),
        format: "yyyy-mm-dd hh:ii",
        weekStart: app_cfg_first_day_of_week,
        pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left"),
        clearBtn: true,
        endDate: $(this).attr('data-date-end-date'), 
        startDate: $(this).attr('data-date-start-date'),
    });
 })
      
  $( "textarea.editor" ).each(function() { use_editor($(this).attr('id'),false) });
 	$( "textarea.full-editor" ).each(function() { use_editor_full($(this).attr('id'),false,$(this).attr('editor-height')) });
  $( "textarea.editor-auto-focus" ).each(function() { use_editor($(this).attr('id'),true) });
       
   $('.colorpicker-default').colorpicker()
   
   $('[data-toggle="tooltip"]').tooltip()       
   
   appHandleChosen();
   
   appHandleSelectAll();
   
   appHandleNumberInput();
   
   $('[data-hover="dropdown"]').dropdownHover();
   
	 if(!$('.modal-collapse').hasClass('active'))
	 {	 
		 $('.modal-collapse').addClass('active')
		 
	   $('.modal-collapse').click(function(){
	  	  if(!$('.modal-backdrop').hasClass('modal-collapsed'))
	  	  {
	  	  	$('.modal-backdrop').addClass('modal-collapsed')
	  	  	$('.modal-scrollable').addClass('modal-collapsed')
	  	  }
	  	  else
	  	  {
	  	  	$('.modal-backdrop').removeClass('modal-collapsed')
	  	  	$('.modal-scrollable').removeClass('modal-collapsed')
	  	  }
	  	  
	  	  jQuery(window).resize();
	   })
	 }                        
}

//autoreplace comma to dot
function appHandleNumberInput()
{
	$('.number').keyup(function(){
		$(this).val($(this).val().replace(',','.'));
	})	
	
	$('.numeric-fields').keyup(function(){
		$(this).val($(this).val().replace(',','.'));
	})
	
}

function appHandleAttachmentsDelete(field_id,delete_file_url,session_token)
{
	$('.delete_attachments_checkbox').click(function(){
			
		//confirm delation
			if($(this).attr('data-confirm-delation')==1)
			{
				if(!confirm(i18n['TEXT_ARE_YOU_SURE']))
				{
					return false;
				}
			}
			
			//fade file row
			row_id = $(this).attr('data-row-id');
			$('.'+row_id).fadeOut();
												
			filename = $(this).attr('data-filename');
														
			//dalete attached file
			$.ajax({
				method: 'POST',
				url: delete_file_url,
				data: {field_id:field_id, form_session_token:session_token, filename:filename}
			}).done(function(){
				//reload atttachment list. Need to do this action if there are some required fields
				eval('uploadifive_oncomplate_filed_'+field_id+'()')
			})		
	})	
}

function appHandleSelectAll()
{
  $('.select-all').focus(function() {
	    this.select();
	}).mouseup(function() {
	    return false;
	});	
}

function appHandleChosen()
{
  $('.chosen-select').each(function(){      
      width = '100%';

      if(!is_mobile)
      {
      	if($(this).hasClass('input-small')) width = '120px';
      	if($(this).hasClass('input-medium')) width = '240px';
        if($(this).hasClass('input-large')) width = '320px';
      	if($(this).hasClass('input-xlarge')) width = '480px';
      }
      
      $(this).chosen({width: width,
                      include_group_label_in_selected: true,
                      search_contains: true,
                      no_results_text:i18n['TEXT_NO_RESULTS_MATCH'],
                      placeholder_text_single:i18n['TEXT_SELECT_AN_OPTION'],
                      placeholder_text_multiple:i18n['TEXT_SELECT_SOME_OPTIONS']
                      }).chosenSortable();
   })
}

function update_crud_checkboxes(view_access,group_id)
{
  if(view_access=='')
  {    
    $('.crud_'+group_id).css('display','none')
  }
  else
  {
    $('.crud_'+group_id).css('display','block')
  }
}

function set_access_to_all_fields(access, group_id)
{
  if(access!='')
  {
    $( ".access_group_"+group_id).each(function() {
      $(this).val(access) 
    });
  }
}

function listing_reset_search(listing_container)
{
  $('#'+listing_container+'_search_keywords').val('')
  $('#'+listing_container+'_search_reset').val('true')
  load_items_listing(listing_container,1)
}  

function listing_order_by(listing_container,fields_id,clause)
{
	//skip order click event if resizable
	if(is_resizable_process)
	{		
		setTimeout(function(){
			is_resizable_process = false			
		},100);
		
		return false;
	}
	
  if(app_key_ctrl_pressed)
  {
    order_fields = $('#'+listing_container+'_order_fields').val().split(',');
    is_in_order = false;
    for(var i=0;i<order_fields.length;i++)
    {
      if(order_fields[i]==fields_id+'_asc' || order_fields[i]==fields_id+'_desc')
      {
        order_fields[i]=fields_id+'_'+clause;
        is_in_order = true;
      }
    }
    
    if(is_in_order)
    {
      $('#'+listing_container+'_order_fields').val(order_fields.join(','))    
    }
    else
    {
      $('#'+listing_container+'_order_fields').val($('#'+listing_container+'_order_fields').val()+','+fields_id+'_'+clause)
    }
  }
  else
  {
    $('#'+listing_container+'_order_fields').val(fields_id+'_'+clause)
  }
  
  $('#'+listing_container+'_order_fields').attr('is_changed',1)
    
  load_items_listing(listing_container, 1);
} 

function select_all_by_classname(id,class_name)
{
  if($('#'+id).attr('checked'))
  {      
    $('.'+class_name).each(function(){            
      $(this).attr('checked',true)
      $('#uniform-'+$(this).attr('id')+' span').addClass('checked')          
    })
  }
  else
  {        
    $('.'+class_name).each(function(){      
      $(this).attr('checked',false)
      $('#uniform-'+$(this).attr('id')+' span').removeClass('checked')
    })
  } 
}

function unchecked_all_by_classname(class_name)
{
	$('.'+class_name).each(function(){      
    $(this).attr('checked',false)
    $('#uniform-'+$(this).attr('id')+' span').removeClass('checked')
  })
}

function checked_all_by_classname(class_name)
{
	$('.'+class_name).each(function(){      
    $(this).attr('checked',true)
    $('#uniform-'+$(this).attr('id')+' span').addClass('checked')
  })
}

function app_search_item_by_id()
{
  $('#search_item_by_id_result').addClass('ajax-loading');
  url = $('#search_item_by_id_form').attr('action');
  id = $('#search_item_by_id').val();
  related_entities_id = $('#search_item_by_id_button').attr('data-related-entities-id');
  
  
  $('#search_item_by_id_result').load(url,{id:id,related_entities_id:related_entities_id},function(){
    $('#search_item_by_id_result').removeClass('ajax-loading');
  })
  return false;
}


//hande listing horisontal scroll bar
$(function(){
  $( window ).resize(function() {
    $('.entity_items_listing').each(function(){                      
       app_handle_listing_horisontal_scroll($(this))
    })
  });
})

function app_handle_listing_horisontal_scroll(listing_obj)
{	  
  //get table object   
  table_obj = $('.table',listing_obj);
  
  //get count fixed collumns params
  count_fixed_collumns = table_obj.attr('data-count-fixed-columns')
  
  //check if no records found
  has_colspan = $('td',table_obj).attr('colspan');
                     
  if(count_fixed_collumns>0 && !has_colspan)
  {
    //get wrapper object
    var wrapper_obj = $('.table-wrapper',listing_obj);
    wrapper_obj.addClass('table-wrapper-css');
    
    wrapper_left_margin = 0;
    
    table_collumns_width = new Array();    
    table_collumns_margin = new Array();
    
    //remove heading class to calculate correct width
    $('td',table_obj).removeClass('item_heading_td');
    
    //remove width from th if was setup previously
    $('th',table_obj).css({'width':''});
    
    //calculate wrapper margin and fixed column width
    $('th',table_obj).each(function(index){
       if(index<count_fixed_collumns)
       {
         wrapper_left_margin += $(this).outerWidth();
         table_collumns_width[index] = $(this).outerWidth();
       }
    })
    
    //calcualte margin for each column  
    $.each( table_collumns_width, function( key, value ) {
      if(key==0)
      {
        table_collumns_margin[key] = wrapper_left_margin;
      }
      else
      {
        next_margin = 0;
        $.each( table_collumns_width, function( key_next, value_next ) {
          if(key_next<key)
          {
            next_margin += value_next;
          }
        });
        
        table_collumns_margin[key] = wrapper_left_margin-next_margin;
      }
    });
    
    //set margin direction
    if(app_language_text_direction=='rtl')
    {
      margin_direction = 'right';
    }
    else
    {
      margin_direction = 'left';
    }
     
    //set wrapper margin               
    if(wrapper_left_margin>0)
    {
      wrapper_obj.css('cssText','margin-'+margin_direction+':'+wrapper_left_margin+'px !important; width: auto')
      
      wrapper_obj.scrollLeft(0);
      
      //there is conflict in Firefox 46.0.1 with current scroll and popover
      //<td> is automatically shifted by scroll value
      if(jQuery.browser.mozilla)
      {
	    	$('[data-toggle="popover"]',wrapper_obj).hover(function(){
	    		var current_scroll_left = parseInt(wrapper_obj.scrollLeft());
	    		
	    		$('.table-fixed-cell',wrapper_obj).each(function(){
	    			if(!$(this).hasClass('ff-fix-scroll'))
	    			{    				    		
	    				current_margin = parseInt($(this).attr('data-current-margin'))
	    				current_margin = (margin_direction=='left' ? current_margin+current_scroll_left : current_margin-current_scroll_left)
	    				$(this).css('margin-'+margin_direction,current_margin+'px')
	    				$(this).addClass('ff-fix-scroll')    				    				
	    			}
	    		})
	    	})
    	  
	    	//remove fix
	    	$(wrapper_obj).scroll(function(){
	    		$('.ff-fix-scroll',this).removeClass('ff-fix-scroll')	    				    				    		
	    	})
      }
      //end of Firefox fix
      
    }
    
    //set position for fixed columns
    $('tr',table_obj).each(function(row_index){  
      
      //get current row height
      current_row_height = $(this).outerHeight();
      
      //set height for row (issue with safari)
      $(this).css('height',current_row_height)
                                   
      $('th,td',$(this)).each(function(index){
                                                        
         //set position 
         if(index<count_fixed_collumns)
         { 
           //set height for fixed td
           $(this).css('height',current_row_height)
                                           
           $(this).css('position','absolute')
                  .css('margin-'+margin_direction,'-'+table_collumns_margin[index]+'px')
                  .css('width',table_collumns_width[index])
                  .attr('data-current-margin','-'+table_collumns_margin[index])
                  
           $(this).addClass('table-fixed-cell')
           
           if(row_index==0)
           {
             $(this).addClass('table-fixed-cell-first-row')
           }
         }
         
      })
    })   
     
  }
}     

function app_handle_listing_fixed_table_header(listing_obj,module_path)
{
	if(module_path!='items/items' && module_path!='reports/view' && module_path!='ext/funnelchart/view') return false
	
	//get table object   
  var table_obj = $('.table',listing_obj);
	
	if(table_obj.attr('data-count-fixed-columns')>0) return false
	
	is_fixed_head = table_obj.attr('data-fixed-head')
	
	if(!is_fixed_head || is_fixed_head==0) return false;
	
	$('td',table_obj).removeClass('fieldtype_textarea');
      
  offset_top_original = $('thead',table_obj).offset().top;
  
  
  offset_top = $('thead',table_obj).offset().top-$('.header').height();
  
  if($(window).width()<973)
  {
  	offset_top = offset_top+50;
  }      
    
  //hander scrol action
  $(window).bind('scroll', function() {
  	var scrollTop = $(this).scrollTop();
  	
  	if(scrollTop>offset_top)
  	{
  		$('thead',table_obj).css('transform','translateY(' + (scrollTop-offset_top) + 'px)');
  		$(table_obj).addClass('thead-transform')
  	}	
  	else
  	{
  		$('thead',table_obj).css('transform','none');
  		$(table_obj).removeClass('thead-transform')
  	}  	  	
  });
  
  //handle onload action
  var scrollTop = $(window).scrollTop();
  
  if(scrollTop>offset_top)
	{
		$('thead',table_obj).css('transform','translateY(' + (scrollTop-offset_top) + 'px)');
		$(table_obj).addClass('thead-transform')
	}	
	else
	{
		$('thead',table_obj).css('transform','none');
		$(table_obj).removeClass('thead-transform')
	}     
}

function app_handle_listing_resizer(listing_obj, update_url)
{
	var table_obj = $('.table',listing_obj);
	
	data_resizable = table_obj.attr('data-resizable');	
	if(!data_resizable || data_resizable==0) return false;
	
	//autocalculate table width in first load
	if($(table_obj).attr('data-has-resizable-width')==1)
	{
		var table_width = 0;
		$('th',table_obj).each(function(){
			if($(this).hasClass('multiple-select-action-th') || $(this).hasClass('fieldtype_action-th'))
				table_width = table_width+parseInt($(this).css('width').replace('px',''));		
		})
		
		//using fixed column value from database
		table_width = table_width+parseInt($(table_obj).attr('data-resizable-width'))
		
		$(table_obj).css('cssText','width:'+table_width+'px !important');
	}
		
	
	var drag_col_width = {};
	
  $("th:not(.multiple-select-action-th,.fieldtype_action-th,.table-fixed-cell-first-row)",table_obj)
      .prepend("<div class='resizer'></div>")
      .css({ position: "relative" })
      .resizablebox({
          resizeHeight: false,
          // we use the column as handle and filter
          // by the contained .resizer element
          handleSelector: "",
          onDragStart: function (e, $el, opt) { 
          		          		          	
          		//reset col width array
          		drag_col_width = {}
          		
          		//rset min-widht          		
          		if($el.css('min-width'))
          		{
          			$el.css('width',$el.width())
          			$el.css('min-width','')
          		}
          		
              // only drag resizer
              if (!$(e.target).hasClass("resizer"))
                  return false;
              return true;
          },
          onDrag: function(e, $el, newWidth, newHeight, opt)
          { 
          	
          	//reset drag if widht <30
          	if(newWidth<30)
          		return false;
          	
          	el_field_id = $($el).attr('data-field-id')
          	
          	table_width = newWidth;
          	          	          	
          	$('th',table_obj).each(function(){
          		
          		field_id = $(this).attr('data-field-id');
          		
          		if(el_field_id!=field_id)
          		{
          			//prepare col width array
          			if(!drag_col_width[field_id])
          			{
          				drag_col_width[field_id] = parseInt($(this).css('width').replace('px',''));
          			}
          			
          			//use fixed value from array
          			table_width = table_width+drag_col_width[field_id]
          		          			
          		}          		          		
          	})
                    	          	          	          
          	$(table_obj).css('cssText','width:'+table_width+'px !important');
          	          	          	          	          	          	          	
          },
          onDragEnd:function (e, $el, opt) {
          	//alert(update_url)
          	listing_col_width = {};
          	$('th.resizable',table_obj).each(function(){          		
          		if($(this).attr('data-field-id').length)          			
          		listing_col_width[$(this).attr('data-field-id')] = $(this).css('width').replace('px','');
          		          		
          	})
          	          	
          	listing_col_width = JSON.stringify(listing_col_width);
          	          	                    	         
          	$.ajax({
      				method: 'POST',
      				url: update_url,
      				data: {listing_col_width:listing_col_width}
      			})
      			
      			/*
      			slimScroll = document.getElementById('slimScroll')
      			// calculate scrollbar height and make sure it is not too small
				    barWidth = Math.max(($('.slimScroll').outerWidth() / slimScroll.scrollWidth) * $('.slimScroll').outerWidth(), 30);
				    barX.css({ width: barWidth + 'px' });
				    railX.css('width',slimScroll.clientWidth)
				    
				    var display = (slimScroll.scrollWidth>slimScroll.clientWidth) ? 'block' : 'none';
				    barX.css({ display: display });
			      railX.css({ display: display });
			      
				    if(display=='none')
				    {				    	
				      $('.slimScroll').css('padding-bottom',0)
				    }
				    else
				    {
				    	$('.slimScroll').css('padding-bottom',barX.height());
				    }
				    */
          	          	      			
          },
      
      });
  
  $('.resizer').mousedown(function(e){        
  		if(e.which === 1)
  		{  			
  			is_resizable_process = true  		
  		}
   });
  
}

function app_handle_listing_slimscroll(listing_obj,module_path)
{	
	
	return false; //set off slimscroll
	
	if(module_path!='items/items' && module_path!='reports/view') return false;
	      
	$('th.resizable',listing_obj).click(function(){
		$('html, body').animate({scrollTop: '0px'}, 0);		
	})
	
	if(!document.getElementById('slimScroll') || is_mobile) return false; 
				
	slimScroll = document.getElementById('slimScroll') 
		
	railX = $('.tableScrollRailX');
	barX = $('.tableScrollBarX');
	
	$('.slimScroll').css('overflow-x','hidden');
	$('.slimScroll').css('padding-bottom',barX.height());
						
		// calculate scrollbar height and make sure it is not too small
    barWidth = Math.max(($('.slimScroll').outerWidth() / slimScroll.scrollWidth) * $('.slimScroll').outerWidth(), 30);
    barX.css({ width: barWidth + 'px' });
    railX.css('width',slimScroll.clientWidth)

    // hide scrollbar if content is not long enough
    // var display = barWidth == me.outerWidth() ? 'none' : 'block';
    var display = (slimScroll.scrollWidth>slimScroll.clientWidth) ? 'block' : 'none';
    barX.css({ display: display });
    railX.css({ display: display });
    
    if(display=='none')
    {    	
      $('.slimScroll').css('padding-bottom',0)
    }
                
    //shift barX if there are fixed columsn
    count_fixed_collumns = $('.table',listing_obj).attr('data-count-fixed-columns')                    
    if(count_fixed_collumns>0)
    {        	
    	barX.css({ 'margin-left': parseInt($('.slimScroll').css('margin-left'))+15 });
    	railX.css({ 'margin-left': parseInt($('.slimScroll').css('margin-left'))+15 });
    }	
    	  	      
   //drag bar and move content 
		barX.on("mousedown", function(e) {
			//alert(1)
			
			isDragg = true;
      t = parseFloat(barX.css('left'));
      pageX = e.pageX;
            
      $(document).on("mousemove.slimscrollX", function(e){
        currLeft = t + e.pageX - pageX;
        barX.css('left', currLeft);
        //scrollContent(0, 0, barX.position().left);// scroll content
                     
        var maxLeft = $('.slimScroll').outerWidth() - barX.outerWidth();
        
     // move bar with mouse wheel
        deltaX = parseInt(barX.css('left'));

        // move bar, make sure it doesn't go out
        deltaX = Math.min(Math.max(deltaX, 0), maxLeft);

        deltaX =  Math.ceil(deltaX) ;
        // scroll the scrollbar
        barX.css({ left: deltaX + 'px' });
        
     // calculate actual scroll amount
        percentScrollX = parseInt(barX.css('left')) / ($('.slimScroll').outerWidth() - barX.outerWidth());
        deltaX = percentScrollX * (slimScroll.scrollWidth - $('.slimScroll').outerWidth());
        
        $('.slimScroll').scrollLeft(deltaX);
        
      });

      $(document).on("mouseup.slimscrollX", function(e) {
        isDragg = false;
        //hideBarX();
        $(document).unbind('.slimscrollX');
      });
      return false;
    }).on("selectstart.slimscrollX", function(e){
      e.stopPropagation();
      e.preventDefault();
      return false;
    });
		
//handle onload action	
		var table_scroll_top = $('.tableScrollRailX').offset().top; 
	  var translateY = $(window).height()-table_scroll_top+$(window).scrollTop()-$('.tableScrollRailX').height();
	  	    
	  if(translateY<0)
		{
	  	barX.css('transform','translateY(' + (translateY) + 'px)');			
	  	railX.css('transform','translateY(' + (translateY) + 'px)');
		}	
		else
		{
			barX.css('transform','none');			
			railX.css('transform','none');
		}
	    		
//hander scrol action
  $(window).bind('scroll', function() {  
  	translateY = $(window).height()-table_scroll_top+$(this).scrollTop()-$('.tableScrollRailX').height();
  	  	
	  if(translateY<0)
		{
	  	barX.css('transform','translateY(' + (translateY) + 'px)');			
	  	railX.css('transform','translateY(' + (translateY) + 'px)');
		}	
		else
		{
			barX.css('transform','none');			
			railX.css('transform','none');
		}
  })
  
 
//hadle page resiz action  
  $(window).resize(function(){
  	var translateY = $(window).height()-table_scroll_top+$(this).scrollTop()-$('.tableScrollRailX').height();   
	  if(translateY<0)
		{
	  	barX.css('transform','translateY(' + (translateY) + 'px)');			
	  	railX.css('transform','translateY(' + (translateY) + 'px)');
		}	
		else
		{
			barX.css('transform','none');			
			railX.css('transform','none');
		}
  	  	
 // calculate scrollbar height and make sure it is not too small
    barWidth = Math.max(($('.slimScroll').outerWidth() / slimScroll.scrollWidth) * $('.slimScroll').outerWidth(), 30);
    barX.css({ width: barWidth + 'px' });
    railX.css('width',slimScroll.clientWidth)
    
    var display = (slimScroll.scrollWidth>slimScroll.clientWidth) ? 'block' : 'none';
    barX.css({ display: display });
    railX.css({ display: display });
    
    (display=='none') ? $('.slimScroll').css('padding-bottom',0): $('.slimScroll').css('padding-bottom',barX.height()); 
        
  })  
  
}

function ckeditor_images_content_prepare()
{
  $('.ckeditor-images-content-prepare .fieldtype_textarea_wysiwyg img').addClass('ckeditor-images-content');
    
  $('.ckeditor-images-content-prepare .fieldtype_textarea_wysiwyg img').click(function(){
     var src = $(this).attr('src');
     $.fancybox.open(
        {
            href : src,                            
        })
  });
  
} 

function delete_filters_templates(id)
{
	url =	$('.a-templates-'+id).attr('data-url');
	
	$.ajax({
		type:'POST',
		url: url		
	})
	
	$('.li-templates-'+id).hide();	
}


function setCookie(cname, cvalue, exdays) 
{
  var d = new Date();
  
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    
  var expires = "expires="+d.toUTCString();
  
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) 
{
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i = 0; i < ca.length; i++) 
  {
      var c = ca[i];
      while (c.charAt(0) == ' ') 
      {
          c = c.substring(1);
      }
      
      if (c.indexOf(name) == 0) 
      {
          return c.substring(name.length, c.length);
      }
  }
  return "";
}

function fc_calendar_button(calendar_id)
{
	$('.fc-calendarButton-button').datepicker({
		rtl: App.isRTL(),
		autoclose: true,
		weekStart: app_cfg_first_day_of_week,
		format: 'yyyy-mm-dd',						
		startView: "months", 
    minViewMode: "months"});
	
	$('.fc-calendarButton-button').on("changeDate", function() {					   
	    var d = $('.fc-calendarButton-button').datepicker('getFormattedDate')					    
		 	$('#'+calendar_id).fullCalendar('gotoDate', d );
	});
}

function app_handle_submodal_open_btn()
{
	$('.btn-submodal-open').click(function(){

		$(this).before('<div class="ajax-loading-small"></div>');
		
		//set paretn container
		if(!$('.items-form-conteiner').hasClass('paretn-items-form'))
		{
			$('.items-form-conteiner').addClass('paretn-items-form')
		}	

		//set sub containter
		if(!$('#sub-items-form').length)
		{				
			$('.paretn-items-form').after('<div id="sub-items-form" data-field-id="'+$(this).attr('data-field-id')+'" data-parent-entity-item-id="'+$(this).attr('data-parent-entity-item-id')+'"></div>');
		}

		//load sub form
		$('#sub-items-form').load($(this).attr('data-submodal-url'), function(){ 
			$('.ajax-loading-small').remove()
			$('.paretn-items-form').hide()
			$('#sub-items-form .autofocus').focus();
		});	
	})	
}

function isIframe () {
  try {
      return window.self !== window.top;
  } catch (e) {
      return true;
  }
}

function app_handle_forms_fields_display_rules(container,fields_id, fileds_type, fields_value, is_multiple)
{	
	//prepare field value
	if(fields_value!==false)
	{				
		var field_val = fields_value.split(',');		
	}
	else
	{	
				
		//skip rules if no field (filed can be hidden in public form or public registration)
		if(!$('.field_'+fields_id).length)
		{
			return false;	
		}
		
		if(fileds_type=='fieldtype_boolean')
		{
			var field_val = ($('#fields_'+fields_id).val()=='true' ? '1':'0');			
		}
		//prepare value for radioboxes
		else if(fileds_type=='fieldtype_radioboxes')
		{
			if($('.field_'+fields_id+':checked').length)
			{
				var field_val = $('.field_'+fields_id+':checked').val();		
			}
			else
			{
				var field_val = '';
			}				
		}
		//prepare value for checkboxes
		else if($('.field_'+fields_id).attr('type') == 'checkbox')
		{
			var field_val = new Array();
			$('.field_'+fields_id+':checked').each(function(){				
				field_val.push($(this).val());
			})			
		}
		//prepare value for dropdown
		else
		{
			var field_val = $('#fields_'+fields_id).val();						
		}
	}
			
	container = (container.length>0 ? '#'+container : '');
	
	//if multiple dropdown then hidde all visible fields in rules and show all hidden filed
	//then we will check all rules for these fields and apply them
	$(container+' .disply-fields-rules-'+fields_id).each(function(){
		
		choices = $(this).attr('data-choices').split(',')
		type = $(this).attr('data-type') 
		handle_fields = $(this).val().split(',')		
		//
		if(($('.field_'+fields_id).hasClass('chosen-select') && $('.field_'+fields_id).attr('multiple')=='multiple') || $('.field_'+fields_id).attr('type')=='checkbox' || is_multiple)
		{
			for(i=0;i<handle_fields.length;i++)
			{
				//hide all visible field
				if(type=='visible')
				{
					$('.form-group-'+handle_fields[i]).hide()
					$('.field_'+handle_fields[i]).addClass('ignore-validation')
				}
				//show all hidden filed
				else
				{
					$('.form-group-'+handle_fields[i]).fadeIn()
					$('.field_'+handle_fields[i]).removeClass('ignore-validation')
				}
			}
		}
		
	});
			
	//prepare fields rules
	$(container+' .disply-fields-rules-'+fields_id).each(function(){
							
		choices = $(this).attr('data-choices').split(',')
		type = $(this).attr('data-type') 
		handle_fields = $(this).val().split(',')		

					
		//multiple dropdown returns null
		if(field_val==null)
		{
			field_val = '0';
		}
		
		//convert valu to array
		if(!Array.isArray(field_val))
		{
			field_val = field_val.split(',');
		}
	
		//alert(field_val)
		
		//check all values from array		
		for(v=0; v<field_val.length;v++)
		{			
			//apply fields rules if values is selected or value is empty
			if($.inArray(field_val[v],choices)!=-1 || field_val[v]=='' || field_val[v]==0)
			{												
				for(i=0;i<handle_fields.length;i++)
				{												
					//hide fields if type hidden or value is empty
					if(type=='hidden' || field_val[v]=='' || field_val[v]==0)
					{					
						$('.form-group-'+handle_fields[i]).hide()
						$('.field_'+handle_fields[i]).addClass('ignore-validation')					
					}
					else
					{
						$('.form-group-'+handle_fields[i]).fadeIn()
						$('.field_'+handle_fields[i]).removeClass('ignore-validation')
					}
					
				}
			}
		}
		
	})
	
	jQuery(window).resize();
}

function app_handle_scrollers()
{
	isRTL = false;
	
	if ($('body').css('direction') === 'rtl') {
    isRTL = true;
	}
	
	$('.scroller').each(function () {
    var height;
    if ($(this).attr("data-height")) {
        height = $(this).attr("data-height");
    } else {
        height = $(this).css('height');
    }
    $(this).slimScroll({
        size: '7px',
        color: ($(this).attr("data-handle-color")  ? $(this).attr("data-handle-color") : '#a1b2bd'),
        railColor: ($(this).attr("data-rail-color")  ? $(this).attr("data-rail-color") : '#333'),
        position: isRTL ? 'left' : 'right',
        height: height,
        alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true : false),
        railVisible: ($(this).attr("data-rail-visible") == "1" ? true : false),
        disableFadeOut: true
    });
	});
}

function random_value(value_length) 
{
  var text = "";
  
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

  for (var i = 0; i < value_length; i++)
  {
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  }
  
  return text;
}

function app_reset_date_range_input(class_name,from,to)
{	
	$('.'+class_name+' #'+from).val('')
	$('.'+class_name+' #'+to).val('')  	
} 

function app_currency_converter(form_id)
{
	//use default convertor
	app_currency_converter_grouped(form_id, '.currency-field');
	
	//group convertor by fields if they have more then 1 currency setup
	var used_group_id = new Array();
	
	$('.currency-field-grouped').each(function(){
		if($.inArray($(this).attr('data-field-id'),used_group_id)==-1)
		{
			used_group_id.push($(this).attr('data-field-id'));
			
			app_currency_converter_grouped(form_id, '.currency-field-'+$(this).attr('data-field-id'));	
		}				
	})
}

function app_currency_converter_grouped(form_id, group_name)
{
	$(form_id+' '+group_name).keyup(function(){
		if($(this).val().length>0)
		{
			if($(this).attr('data-currency-default')=='1')
			{
				var default_val = $(this).val();											
			}
			else
			{
				var default_val = ($(this).val()/$(this).attr('data-currency-value'));
			}	
			
			var skip_id = $(this).attr('id');
			
			$(form_id+' '+group_name).each(function(){
				if($(this).attr('id')!=skip_id)
				{	
					field_val = $.number(default_val*$(this).attr('data-currency-value'),2,'.','');
					$(this).val(field_val)
				}
			})
		}
	})
}

function app_move_caret_to_end(el) 
{
	var html = $("#"+el).val();
	$("#"+el).focus().val("").val(html);
}

function number_format (number, decimals, dec_point, thousands_sep) {
  // Strip all characters but numerical ones.
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
      prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
      sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
      dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
      s = '',
      toFixedFix = function (n, prec) {
          var k = Math.pow(10, prec);
          return '' + Math.round(n * k) / k;
      };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
      s[1] = s[1] || '';
      s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

function app_check_active_form_tab(form_name)
{
	if($(form_name+' .nav-tabs').length)
	{
		var check = false;
		
		$(form_name+' .nav-tabs li').each(function(){
			if($(this).hasClass('active')) check = true
		})
		
		if(!check)
		{
			$(form_name+' .nav-tabs li:first').addClass('active')
			$(form_name+' .tab-content .tab-pane:first').addClass('active in')
		}
		
	}
}

function is_valid_email(email) {
  var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(String(email).toLowerCase());
}

function textarea_insert_at_caret(areaId, text)
{
	var txtarea = document.getElementById(areaId);
  var scrollPos = txtarea.scrollTop;
  var caretPos = txtarea.selectionStart;

  var front = (txtarea.value).substring(0, caretPos);
  var back = (txtarea.value).substring(txtarea.selectionEnd, txtarea.value.length);
  txtarea.value = front + text + back;
  caretPos = caretPos + text.length;
  txtarea.selectionStart = caretPos;
  txtarea.selectionEnd = caretPos;
  txtarea.focus();
  txtarea.scrollTop = scrollPos;
}






