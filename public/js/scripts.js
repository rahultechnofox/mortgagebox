'use strict';

function showLoader() {
    $('#overlay').fadeIn();
}
function hideLoader() {
    $('#overlay').fadeOut();
}
function getFormData($form){
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};
    $.map(unindexed_array, function(n, i){
        indexed_array[n['name']] = $.trim(n['value']);
    });
    return indexed_array;
}
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
function myToastr(msg,type){
    toastr.remove();
    if(type == 'error'){
        toastr.error(msg);
    }else if(type == 'success'){
        toastr.success(msg);
    }
}
function getSubCategory(id) {
    var data = {
        'parent_id' : id
    };
    $.ajax({
        type: 'get',
        url: base_url + '/admin/get-subcategory',
        data: data,
        success: function(response) {
            if (response.success) {
                for(var c=0; c<response.data.length; c++){
                      var html = `<option value="`+response.data[c].id+`">`+response.data[c].category_name+`</option>`;
                      $('#sub_category').append(html);
                  }
            }
        }
    });
}

function resetFilter() {
  var newURL = location.href.split("?")[0];
    window.history.pushState('object', document.title, newURL);
    location.reload();
}

function triggerFileInput(className){
    $('.'+className).click();
}

/************* User Management **************/
var user_image;
function uploadImage(input,previewid,id,url,folder) {
    $('#createBtn').prop('disabled', true);
    $('#createBtn').html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if(input.files && input.files[0]){
        var imgPath = input.files[0].name;
        console.log(imgPath);
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if(extn == "gif" || extn == "png')}}" || extn == "jpg" || extn == "jpeg") {
            if(typeof (FileReader) != "undefined"){
                // $("#preloader").show();
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                console.log(reader);
                reader.onload = function (e) {
                    $("."+previewid+"").attr('src',e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    fd.append('folder',folder);
                    console.log(base_url+'/'+url);
                    $.ajax({
                        url: base_url+'/'+url,
                        data:fd,
                        processData:false,
                        contentType:false,
                        type:'POST',
                        dataType:'json',
                        success:function(data){
                            setTimeout(function(){
                                user_image = data.data;
                                if(user_image!=undefined){
                                    $('#createBtn').prop('disabled', false);
                                }
                            }, 10);
                        }
                    })
                };
            }else{
                console.log("This browser does not support FileReader.");
            }
        }else{
            console.log("Please select only images");
        }
    }
}

function removeImage(previewid){
    var url = base_url+"/upload/users/no-image.png";
    $("."+previewid+"").attr('src',url);
    user_image = "null";
}

function addUser(formId,submitType){
    var $form = $("#"+formId);
    var data = getFormData($form);
    var url ="";
    if(submitType=='add'){
        url = base_url +"/admin/users/create";
    }else{
        url = base_url +"/admin/users/update";
    }
    if(user_image!=''){
        data.image = user_image;
    }
    if(data.first_name == ''){
        myToastr('Enter first name','error');
    }else if(data.last_name == ''){
        myToastr('Enter last name','error');
    }else if(data.email == ''){
        myToastr('email email','error');
    }else if(data.password == ''){
        myToastr('Enter password','error');
    }else if(data.username == ''){
        myToastr('Enter username','error');
    }else if(data.gender == ''){
        myToastr('Enter gender','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}
/************* User Management **************/

/************* University Management **************/
function addUniversity(formId,submitType){
    var $form = $("#"+formId);
    var data = getFormData($form);
    var url ="";
    if(submitType=='add'){
        url = base_url +"/admin/university/create";
    }else{
        url = base_url +"/admin/university/update";
    }
    if(data.name == ''){
        myToastr('Enter name','error');
    }else if(data.email == ''){
        myToastr('email email','error');
    }else if(data.username == ''){
        myToastr('Enter username','error');
    }else if(data.phone == ''){
        myToastr('Enter phone','error');
    }else if(data.address == ''){
        myToastr('Enter address','error');
    }else if(data.contactno_person == ''){
        myToastr('Enter number of contact person for registration','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function updateUniversity(value,inputType,universityId){
    var $form = $("#editUniversityForm");
    var data = getFormData($form);
    data.id = universityId;
    if(inputType == ''){
        myToastr('Enter '+inputType,'error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: base_url +"/admin/university/update",
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}
var banner_image;
function uploadBannerImage(input,previewid,id,url,folder) {
    $('#createBtn').prop('disabled', true);
    $('#createBtn').html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if(input.files && input.files[0]){
        var imgPath = input.files[0].name;
        console.log(imgPath);
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if(extn == "gif" || extn == "png')}}" || extn == "jpg" || extn == "jpeg") {
            if(typeof (FileReader) != "undefined"){
                // $("#preloader").show();
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                console.log(reader);
                reader.onload = function (e) {
                    $("."+previewid+"").attr('src',e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    fd.append('folder',folder);
                    fd.append('id',id);
                    console.log(base_url+'/'+url);
                    $.ajax({
                        url: base_url+'/'+url,
                        data:fd,
                        processData:false,
                        contentType:false,
                        type:'POST',
                        dataType:'json',
                        success:function(data){
                            setTimeout(function(){
                                banner_image = data.data;
                            }, 10);
                        }
                    })
                };
            }else{
                console.log("This browser does not support FileReader.");
            }
        }else{
            console.log("Please select only images");
        }
    }
}

function removeImage(image,id) {
    var data = {};
    data.image = image;
    data.id = id;
    console.log(data);
    $.ajax({
        url: base_url+'/admin/removeImage',
        data:data,
        type:'POST',
        success:function(data){
            console.log(data);
            // location.reload();
        }
    })
}
/************* University Management **************/

function updatePassword(id){
    var data = {};
    data.id = id;
    data.password = $("#password").val();
    if(data.password == ''){
        myToastr('Enter password','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: base_url+'/admin/updatePassword',
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function deleteCustomer(id) {
    var data = {};
    data.id = id;
    console.log(data);
    $.ajax({
        url: base_url+'/admin/deleteCustomer',
        data:data,
        type:'POST',
        success:function(data){
            console.log(data);
            myToastr(data.message,'success');
            location.href = base_url+"/admin/users";
        }
    })
}

function updateStatus(id,status,url,suspended=false,suspended_reason=""){
    var data = {};
    data.id = id;
    data.status = status;
    if(suspended==true){
        data.suspend_reason = $("#"+suspended_reason).val();
    }
    var api_url = base_url+''+url;
    if(data.status == ''){
        myToastr('Something went wrong please refresh page','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: api_url,
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function validFCANumber(status,id){
    var data = {};
    data.id = id;
    data.status = status;
    console.log(data);
    if(data.status == ''){
        myToastr('Something went wrong please refresh page','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: base_url+'/admin/update-fca-verification-status',
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function addUpdateService(formId){
    var $form = $("#"+formId);
    var data = getFormData($form);
    if(data.name == ''){
        myToastr('Enter name','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: base_url+"/admin/add-update-service",
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function getAudience(id){
    showLoader();
    var data = {};
    if(id!=''){
        data.id = id;
        $.ajax({
            type: 'post',
            url: base_url +"/admin/get-audience",
            data: data,
            success: function (response) {
                var audience = response.data.audience.charAt(0).toUpperCase() + response.data.audience.slice(1);
                $('#audience').val(audience);
                hideLoader();
            }
        });
    }else{
        $('#audience').val('');
    }
}

function getServiceData(id){
    showLoader();
    var data = {};
    data.id = id;
    $.ajax({
        type: 'post',
        url: base_url +"/admin/get-service",
        data: data,
        success: function (response) {
            $('#exampleModalLabel').html('Edit Service');
            $('#id').val(response.data.id);
            $('#name').val(response.data.name);
            if(response.data.parent_id!=0){
                $('#parent_id').val(response.data.parent_id);
                $('.parent_id').show();
            }else{
                $('.parent_id').hide();
            }  
            hideLoader();
        }
    });
}

function resetFaqCategoryForm(){
    $("#faqCategoryForm").closest('form').find("input[type=text], input[type=number], input[type=file], textarea").val("");
    $("#faqCategoryForm").closest('form').find("input[type=checkbox]").removeAttr("checked");
    $('#id').val('');
    $('#audience').val('');
    $('#exampleModalLabel').html('Add Faq Category');
    $("#show_cat_image_add").hide();
}

function addUpdateFaqCategory(formId){
    var $form = $("#"+formId);
    var data = getFormData($form);
    if(category_image!=''){
        data.image = category_image;
    }
    if(data.name == ''){
        myToastr('Enter name','error');
    }else if(data.sub_title == ''){
        myToastr('Enter sub title','error');
    }else if(data.audience == ''){
        myToastr('Select audience','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: base_url+"/admin/add-update-faq-category",
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function getFaqCategoryData(id){
    showLoader();
    var data = {};
    data.id = id;
    $.ajax({
        type: 'post',
        url: base_url +"/admin/get-faq-category",
        data: data,
        success: function (response) {
            $('#exampleModalLabel').html('Edit Faq Category');
            $('#id').val(response.data.id);
            $('#name').val(response.data.name);  
            $('#sub_title').val(response.data.sub_title);  
            if(response.data.audience!=0){
                $('#audience').val(response.data.audience);
                $('.audience_id').show();
            }else{
                $('.audience_id').hide();
            } 
            $("#show_cat_image_add").show();
            $('#image_add').attr('src',response.data.image);
            hideLoader();
        }
    });
}

function resetServiceForm(){
    $("#servicesForm").closest('form').find("input[type=text], input[type=number], input[type=file], textarea").val("");
    $("#servicesForm").closest('form').find("input[type=checkbox]").removeAttr("checked");
    $('#id').val('');
    $('#parent_id').val('');
    $('#exampleModalLabel').html('Add Service');
}

function addUpdateFaq(formId){
    // var $form = $("#"+formId);
    // var data = getFormData($form);
    // console.log(data);
    // if(data.faq_category_id == ''){
    //     myToastr('Select faq category','error');
    //     return false;
    // }else if(data.question == ''){
    //     myToastr('Enter question','error');
    //     return false;
    // }else{
    //     showLoader();
    //     return true;
    //     // $.ajax({
    //     //     type: 'post',
    //     //     url: base_url+"/admin/add-update-faq",
    //     //     data: data,
    //     //     success: function (response) {
    //     //         if(!response.status){
    //     //             hideLoader();
    //     //             myToastr(response.message,'error');
    //     //         }else{
    //     //             location.reload();                   
    //     //             myToastr(response.message,'success');
    //     //         }
    //     //     }
    //     // });
    // }
}

function addNotes(formId){
    var $form = $("#"+formId);
    var data = getFormData($form);
    if(data.notes == ''){
        myToastr('Enter notes','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: base_url+"/admin/add-notes",
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function resetPassword(id){
    showLoader();
    var data = {};
    data.id = id;
    $.ajax({
        type: 'post',
        url: base_url +"/admin/reset-password",
        data: data,
        success: function (response) {
            myToastr(response.message,'success');
            hideLoader();
        }
    });
}

function triggerFileInput(className){
    $('.'+className).click();
}

var category_image;
function uploadFaqCategoryImage(input,previewid,type,id) {
    $('#createBtn').prop('disabled', true);
    if(input.files && input.files[0]){
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if(extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if(typeof (FileReader) != "undefined"){
                $("#preloader").show();
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if(type=="add"){
                        $("#show_cat_image_add").show();
                        $('#'+previewid).attr('src',e.target.result);
                    }else if(type=="update"){
                        $("#image_update_"+id+"").attr('src',e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url:base_url+'/admin/uploadFaqCategoryImage',
                        data:fd,
                        processData:false,
                        contentType:false,
                        type:'POST',
                        dataType:'json',
                        success:function(data){
                            setTimeout(function(){
                                category_image = data.data;
                                if(category_image!=undefined){
                                    $('#createBtn').prop('disabled', false);
                                    if($('#categoryId').val()){
                                        $('#createBtn').html('Update');
                                    }else{
                                        $('#createBtn').html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            }else{
                console.log("This browser does not support FileReader.");
            }
        }else{
            console.log("Please select only images");
        }
    }
}

function selectValue(value){
    if(value!='Other'){
        $("#suspend_reason").val(value);
        $("#suspend_reason").html(value);
        $(".reason").addClass('hide');
    }else{
        $("#suspend_reason").val('');
        $("#suspend_reason").html('');
        $(".reason").removeClass('hide');
    }
}

function resetSuspended(formId){
    $("#"+formId).closest('form').find("input[type=text], input[type=number], input[type=file], textarea").val("");
    $("#reason").val('');
    $("#suspend_reason").html('');
}

function getContactUsData(id){
    showLoader();
    var data = {};
    data.id = id;
    $.ajax({
        type: 'post',
        url: base_url +"/admin/get-contact-us",
        data: data,
        success: function (response) {
            $('#id').val(response.data.id);
            $('#email').val(response.data.email);
            if(response.data.parent_id!=0){
                $('#parent_id').val(response.data.parent_id);
                $('.parent_id').show();
            }else{
                $('.parent_id').hide();
            }  
            hideLoader();
        }
    });
}

function replyContactus(formId){
    var $form = $("#"+formId);
    var data = getFormData($form);
    if(data.message == ''){
        myToastr('Enter message','error');
    }else{
        showLoader();
        $.ajax({
            type: 'post',
            url: base_url+"/admin/reply-contact-us",
            data: data,
            success: function (response) {
                if(!response.status){
                    hideLoader();
                    myToastr(response.message,'error');
                }else{
                    location.reload();                   
                    myToastr(response.message,'success');
                }
            }
        });
    }
}

function takeDecision(id,status){  
    var data = {};
    data.id = id;
    data.spam_status = status;
    showLoader();
    $.ajax({
        type: 'post',
        url: base_url+"/admin/takeDecision",
        data: data,
        success: function (response) {
            if(!response.status){
                hideLoader();
                myToastr(response.message,'error');
            }else{
                location.reload();                   
                myToastr(response.message,'success');
            }
        }
    });
}

function refundPayment(id,status){  
    var data = {};
    data.id = id;
    data.spam_status = status;
    showLoader();
    $.ajax({
        type: 'post',
        url: base_url+"/admin/refundPayment",
        data: data,
        success: function (response) {
            if(!response.status){
                hideLoader();
                myToastr(response.message,'error');
            }else{
                location.reload();                   
                myToastr(response.message,'success');
            }
        }
    });
}

function resetContactUsForm(){
    $("#servicesForm").closest('form').find("input[type=text], input[type=number], input[type=file], textarea").val("");
    $("#servicesForm").closest('form').find("input[type=checkbox]").removeAttr("checked");
    $('#id').val('');
}

var importerobjects = [];
function getPostCodesAutocomplete(){
    $("#post_code").typeahead({
        autoSelect: true,
        delay: 400,
        source: function(query, process) {
            if ($("#post_code").val() != "") {
                var path = base_url + "/admin/postcode-autocomplete";
                var map = {};
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    }
                });
                $.post(path, {
                    term: query
                }, function(data) {
                    importerobjects = [];
                    $.each(data, function(i, object) {
                        map[object.Postcode] = object;
                        importerobjects.push(object.Postcode);
                    });
                    process(importerobjects);
                });
            }
        },
        updater: function(item) {
            console.log(item);
            setTimeout(function() {
                $("#post_code").val(item);
                // post_code_check(item);
            }, 300);
        },
    })
}