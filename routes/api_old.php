<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdviceController;
use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\UserController;

Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);
Route::post('advisorRegister', [ApiController::class, 'advisorRegister']);
Route::get('verifyEmail/{id}', [ApiController::class, 'verifyEmail']);
Route::post('forgotPassword/', [ApiController::class, 'forgotPassword']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [ApiController::class, 'logout']);
    Route::get('get_user', [ApiController::class, 'get_user']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('create', [ProductController::class, 'store']);
    Route::put('update/{product}',  [ProductController::class, 'update']);
    Route::delete('delete/{product}',  [ProductController::class, 'destroy']);
    Route::post('updateAccount/', [ApiController::class, 'updateAccount']);
    Route::post('changePassword/', [ApiController::class, 'changePassword']);
    Route::post('updateAdvisorProfile/',  [ApiController::class, 'updateAdvisorProfile']);
    Route::get('getAdvisorProfile/', [ApiController::class, 'getAdvisorProfile']);
    // Users routes
    Route::post('addNewAdviceArea/',  [ApiController::class, 'addNewAdviceArea']);
    Route::get('getUsersAdviceArea/',  [ApiController::class, 'getUsersAdviceArea']);
    Route::get('getAdviceAreaById/{id}',  [ApiController::class, 'getAdviceAreaById']);
    Route::post('addNotes/',  [ApiController::class, 'addUserNotes']);
    Route::get('getAdviceNotesByAdviceId/{advice_id}',  [ApiController::class, 'getAdviceNotesByAdviceId']);
    Route::post('updateNotes',  [ApiController::class, 'updateNotes']);
    Route::post('closeAdviceAreaNeed/',  [ApiController::class, 'closeAdviceAreaNeed']);
    Route::post('resendActivationMail/',  [ApiController::class, 'resendActivationMail']);
    Route::post('searchMortgageNeeds/',  [ApiController::class, 'searchMortgageNeeds']);
    Route::get('matchLeads/',  [ApiController::class, 'matchLeads']);
    
    Route::post('acceptRejectBid/',  [ApiController::class, 'acceptRejectBid']);
    Route::post('inviteUsers/',  [ApiController::class, 'inviteUsers']);
    Route::get('getAdviseAreaBid/{id}/{status}',  [ApiController::class, 'getAdviseAreaBid']);
    Route::post('startChat/',  [ApiController::class, 'startChat']);
    Route::post('sendMessage/',  [ApiController::class, 'sendMessage']);
    Route::get('advisorAcceptedLeads/',  [ApiController::class, 'advisorAcceptedLeads']);
    Route::get('getRecentMessages/',  [ApiController::class, 'getRecentMessages']);
    Route::post('seenMessages/',  [ApiController::class, 'seenMessages']);
    Route::post('sendAttachment/',  [ApiController::class, 'sendAttachment']);
    Route::post('addOffer/',  [ApiController::class, 'addOffer']);
    Route::post('editOffer/{id}',  [ApiController::class, 'editOffer']);
    Route::post('deleteOffer/{id}',  [ApiController::class, 'deleteOffer']);
    //for user
    Route::post('addReview',  [UserController::class, 'addReview']);
    Route::get('getReviewRating',  [AdvisorController::class, 'getReviewRating']);
    Route::get('selectOrDeclineOffer/{bid_id}/{status}', [ApiController::class, 'selectOrDeclineOffer']);

    // for advisor route

    Route::get('getAdvisorLinks/', [AdvisorController::class, 'getAdvisorLinks']);
    Route::post('setAdvisorLinks/', [AdvisorController::class, 'setAdvisorLinks']);
    Route::get('getAdvisorProfileByAdvisorId/{id}', [AdvisorController::class, 'getAdvisorProfileByAdvisorId']);
    Route::get('getNotificationPreferences', [ApiController::class, 'getNotificationPreferences']);
    Route::post('updateNotificationPreferences/', [ApiController::class, 'updateNotificationPreferences']);
    Route::get('getAdvisorDefaultPreference/', [AdvisorController::class, 'getAdvisorDefaultPreference']);
    Route::post('updateAdvisorDefaultPreference/', [AdvisorController::class, 'updateAdvisorDefaultPreference']);
    Route::post('setRecentMessagesOfChatToRead/', [ApiController::class, 'setRecentMessagesOfChatToRead']);
    Route::post('searchAdvisor/', [AdvisorController::class, 'searchAdvisor']);
    Route::post('updateAdvisorAboutUs/', [AdvisorController::class, 'updateAdvisorAboutUs']);
    Route::post('updateAdvisorGeneralInfo/', [AdvisorController::class, 'updateAdvisorGeneralInfo']);
    Route::get('getAdvisorProductPreference/', [AdvisorController::class, 'getAdvisorProductPreference']);
    Route::post('updateAdvisorProductPreference/', [AdvisorController::class, 'updateAdvisorProductPreference']);
      Route::get('getAdvisorCustomerPreference/', [AdvisorController::class, 'getAdvisorCustomerPreference']);
    Route::post('updateAdvisorCustomerPreference/', [AdvisorController::class, 'updateAdvisorCustomerPreference']);
    Route::get('getAdvisorLocationPreference/', [AdvisorController::class, 'getAdvisorLocationPreference']);
    Route::post('updateAdvisorLocationPreference/', [AdvisorController::class, 'updateAdvisorLocationPreference']);
    Route::get('getAdvisorBillingAddress/', [AdvisorController::class, 'getAdvisorBillingAddress']);
    Route::post('updateAdvisorBillingAddress/', [AdvisorController::class, 'updateAdvisorBillingAddress']);
     Route::get('getAdvisorFirstMessage/', [AdvisorController::class, 'getAdvisorFirstMessage']);
    Route::post('updateFirstMessage/', [AdvisorController::class, 'updateFirstMessage']);
    Route::post('advisorTeam/', [AdvisorController::class, 'advisorTeam']);
    Route::post('updateTeam/', [AdvisorController::class, 'updateTeam']);
    Route::get('getAdvisorTeam/{company_id}', [AdvisorController::class, 'getAdvisorTeam']);
    Route::get('deleteTeam/{team_id}', [AdvisorController::class, 'deleteTeam']);
    Route::post('makeEnquiry', [AdvisorController::class, 'makeEnquiry']);
    Route::get('advisorDashboard', [AdvisorController::class, 'advisorDashboard']);
    Route::post('saveCard', [ApiController::class, 'saveCard']);
    Route::post('createCustomer', [ApiController::class, 'createCustomer']);
    Route::post('getAllCardByCustomer', [ApiController::class, 'getAllCardByCustomer']);
    Route::post('deleteCard', [ApiController::class, 'deleteCard']);
    Route::post('checkoutFromSavedCard', [ApiController::class, 'checkoutFromSavedCard']);
    Route::get('getNotification', [ApiController::class, 'getNotification']);
    Route::get('updateReadNotification', [ApiController::class, 'updateReadNotification']);
    Route::post('searchPostalCode', [ApiController::class, 'searchPostalCode']);
    
    
});

//for interest
Route::post('addAdviceArea/', [AdviceController::class, 'addAdviceArea']);
Route::post('addInterest', [InterestController::class, 'store']);

