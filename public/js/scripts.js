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

function updateStatus(id,status,url){
    var data = {};
    data.id = id;
    data.status = status;
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
    data.id = id;
    $.ajax({
        type: 'post',
        url: base_url +"/admin/get-audience",
        data: data,
        success: function (response) {
            $('#audience').val(response.data.audience);
            hideLoader();
        }
    });
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
    $('#audience_id').val('');
    $('#exampleModalLabel').html('Add Faq Category');
}

function addUpdateFaqCategory(formId){
    var $form = $("#"+formId);
    var data = getFormData($form);
    if(data.name == ''){
        myToastr('Enter name','error');
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
                    // location.reload();                   
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
            if(response.data.audience!=0){
                $('#audience').val(response.data.audience);
                $('.audience_id').show();
            }else{
                $('.audience_id').hide();
            } 
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

$("#for_all_users").on('change', function () {
    var el = $(this);
    $(".user_group").addClass('hide');
    if (el.val() === "university")
    {
        $(".for_university").removeClass('hide');
    }
    else if (el.val() === "school")
    {
        $(".for_school").removeClass('hide');
    }
    else if(el.val() === "ellib")
    {
        $(".for_ellib").removeClass('hide');

    }else if (el.val() === "specific_users")
    {
        $(".for_specific_users").removeClass('hide');
    }
});