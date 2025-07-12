AttachEventListerSE('click','#parent',function() {
  if(scriptJquery('#parent').val() > 0)
    scriptJquery('#show_level').hide();
  else
    scriptJquery('#show_level').show();
});

scriptJquery (document).ready(function (e) {
  scriptJquery ('#addcategory').on('submit',(function(e) {
    var error = false;
    var nameFieldRequired = scriptJquery('#tag-name').val();
    if(!nameFieldRequired){
      scriptJquery('#name-required').addClass('category_field_error');
      error = true;
    }else{
      scriptJquery('#name-required').removeClass('category_field_error');
    }
    
    if(error){
      scriptJquery('html, body').animate({
        scrollTop: scriptJquery('#addcategory').position().top },
        1000
      );
      return false;
    }
    
    scriptJquery('#add-category-overlay').css('display','block');
    e.preventDefault();
    var form = scriptJquery('#addcategory');
    var formData = new FormData(this);
    formData.append('is_ajax', 1);
    scriptJquery .ajax({
    type:'POST',
    url: scriptJquery(this).attr('action'),
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data){
        scriptJquery('#add-category-overlay').css('display','none');
        data = scriptJquery.parseJSON(data); 
        
        parent = scriptJquery('#parent').val();
        if ( parent > 0 && scriptJquery('#categoryid-' + parent ).length > 0 ){ // If the parent exists on this page, insert it below. Else insert it at the top of the list.
          var scrollUpTo= '#categoryid-' + parent;
          scriptJquery( '.admin_table #categoryid-' + parent ).after( data.tableData ); // As the parent exists, Insert the version with - - - prefixed
        }else{
          var scrollUpTo = '#multimodify_form';
          scriptJquery( '.admin_table' ).prepend( data.tableData ); // As the parent is not visible, Insert the version with Parent - Child - ThisTerm					
        }
        if ( scriptJquery('#parent') ) {
          // Create an indent for the Parent field
          indent = data.seprator;
          if(indent != 3)
            form.find( 'select#parent option:selected' ).after( '<option value="' + data.id + '">' + indent + data.name + '</option>' );
        }
        scriptJquery('html, body').animate({
          scrollTop: scriptJquery(scrollUpTo).position().top },
           1000
        );
        scriptJquery('#addcategory')[0].reset();
      },
      error: function(data){
        //silence
      }
    });
  }));
  scriptJquery("#submitaddcategory").on("click", function() {
    scriptJquery("#addcategory").submit();
  });
});

function selectAll() {
  var i;
  var multimodify_form = document.getElementById('multimodify_form');
  var inputs = multimodify_form.elements;
  for (i = 1; i < inputs.length - 1; i++) {
    if (!inputs[i].disabled) {
      inputs[i].checked = inputs[0].checked;
    }
  }
}

AttachEventListerSE('click','#deletecategoryselected',function() {
  var n = scriptJquery(".checkbox:checked").length;
  if(n>0){
    var confirmDelete = confirm(en4.core.language.translate("Are you sure you want to delete the selected categories?"));
    if(confirmDelete){
      var selectedCategory = new Array();
      if (n > 0){
        scriptJquery(".checkbox:checked").each(function(){
          scriptJquery('#categoryid-'+scriptJquery(this).val()).css('background-color','#ffebe8');
          selectedCategory.push(scriptJquery(this).val());
        });
        var scrollToError = false;
        scriptJquery.post(window.location.href,{data:selectedCategory,selectDeleted:'true'},function(response){
          response = scriptJquery.parseJSON(response); 
          var ids = response.ids;
          if(response.diff_ids.length>0){
            scriptJquery('#error-message-category-delete').html("Red mark category can't delete.You need to delete lower category of that category first.<br></br>");
            scriptJquery('#error-message-category-delete').css('color','red');
            scrollToError = true;
          }else{
            scriptJquery('#error-message-category-delete').html("");
            scriptJquery('#error-message-category-delete').css('color','');
          }
          scriptJquery('#multimodify_form')[0].reset();
          if(response.ids){
            //error-message-category-delete;
            for(var i =0;i<=ids.length;i++){
              scriptJquery('select#parent option[value="' + ids[i] + '"]').remove();
              scriptJquery('#categoryid-'+ids[i]).fadeOut("normal", function() {
                scriptJquery(this).remove();
              });
            }
          }
          if(scrollToError){
            scriptJquery('html, body').animate({
              scrollTop: scriptJquery('#addcategory').position().top },
                                               1000
            );
          }
        });
        return false;
      }
    }
  }
});

AttachEventListerSE('click','.deleteCat',function(){
  var id = scriptJquery(this).attr('data-url');
  var confirmDelete = confirm(en4.core.language.translate("Are you sure you want to delete the selected category?"));
  if(confirmDelete){
    scriptJquery('#categoryid-'+id).addClass('category_delete_error');
    var selectedCategory=[id];
    var scrollToError = false;
    scriptJquery.post(window.location.href,{data:selectedCategory,selectDeleted:'true'},function(response){
      response = scriptJquery.parseJSON(response); 
      if(response.ids){
        var ids = response.ids;
        if(response.diff_ids.length>0){
          scriptJquery('#error-message-category-delete').html("Red mark category can't delete.You need to delete lower category of that category first.<br></br>");
          scriptJquery('#error-message-category-delete').css('color','red');
        }else{
          scriptJquery('#error-message-category-delete').html("");
          scriptJquery('#error-message-category-delete').css('color','');
        }
        for(var i =0;i<=ids.length;i++){
          scriptJquery('select#parent option[value="' + ids[i] + '"]').remove();
          scriptJquery('#categoryid-'+ids[i]).fadeOut("normal", function() {
            scriptJquery(this).remove();
          });
        }
        if(scrollToError){
          scriptJquery('html, body').animate({
            scrollTop: scriptJquery('#addcategory').position().top },
                                             1000
          );
        }
      }
    });
  }
});
AttachEventListerSE('click','.openSmoothbox',function(e){
  var url = scriptJquery(this).attr('href');
  openSmoothBoxInUrl(url);
  return false;
});

function openSmoothBoxInUrl(url){
  Smoothbox.open(url);
  return false;
}
