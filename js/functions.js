function showGiftDetailsWindow(giftId, listId)
{
	var currlistId = parseInt(listId);
	var currGiftId = parseInt(giftId);
	$.ajax({
		url: bwURL + '/a_gift_show.php?listId=' + currlistId + '&id=' + currGiftId,
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if(typeof returnedData !== 'undefined') {
				var giftDetails = returnedData;
				$('#gift_details_name').text(giftDetails.name);
				$('#gift_details_added').text(date(bwLng.dateFormat, strtotime(giftDetails.addedDate)));
				if(giftDetails.isBought) {
					$('#gift_details_buy').show();
					$('#gift_details_buy_who').text(giftDetails.boughtByName);
					if(giftDetails.purchaseDate != null) {
						$('#gift_details_buy_date').text(date(bwLng.dateFormat, strtotime(giftDetails.purchaseDate)));
					}
					if(typeof giftDetails.boughtComment !== 'undefined' && giftDetails.boughtComment != null && giftDetails.boughtComment.length > 0) {
						$('#gift_details_buy_comment').show();
						$('#gift_details_buy_comment_text').text(giftDetails.boughtComment);
					} else {
						$('#gift_details_buy_comment').hide();
					}
				} else {
					$('#gift_details_buy').hide();
				}
				giftDetailsDialog.dialog('open');
			} else {
				showFlashMessage('error', 'Internal error');
			}
		}
	});
}

function addCat(listId)
{
	currentListId = parseInt(listId);
	currentCatName = $('#cat_name').val();
	if(currentCatName.length < 2) {
		// Category name too short
		showFlashMessage('error', bwLng.catNameTooShort);
	} else {
		$.ajax({
			type: 'POST',
			url: bwURL + '/a_gifts_mgmt.php?listId=' + currentListId + '&action=add',
			data: {type: 'cat', name: currentCatName},
			dataType: 'json',
			error: function(jqXHR, textStatus, errorThrown) {
				showFlashMessage('error', 'An error occured: ' + errorThrown);
			},
			success: function(returnedData, textStatus, jqXHR) {
				if(returnedData.status == 'success') {
					showFlashMessage('info', returnedData.message);
					$('#cat_name').val('');
					$('#section_add_cat').hide();
					reloadList(currentListId);
					reloadCatsList(currentListId);
				} else {
					showFlashMessage('error', returnedData.message);
				}
			}
		});
	}
}

function confirmDeleteCat(listId, catId)
{
	currentListId = parseInt(listId);
	currentCatId = parseInt(catId);
	$( '#cat_confirm_delete_dialog' ).dialog( 'open' );
	$( '#cat_confirm_delete_dialog' ).dialog({
		width: 400,
		resizable: false,
		modal: true,
		title: bwLng.confirmation,
		buttons: [
			{
				text: bwLng.deleteCategory,
				click: function() { 
					deleteCat(currentListId, currentCatId);
					$(this).dialog('close');
				}
			},
			{
				text: bwLng.cancel,
				click: function() { 
					$(this).dialog('close');
				}
			}
		]
	});
}

function deleteCat(listId, catId)
{
	currentListId = parseInt(listId);
	currentCatId = parseInt(catId);
	$.ajax({
		type: 'POST',
		url: bwURL + '/a_gifts_mgmt.php?listId=' + currentListId + '&action=del',
		data: {type: 'cat', id: currentCatId},
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if(returnedData.status == 'success') {
				showFlashMessage('info', returnedData.message);
				$('#cat_' + currentCatId).remove();
				reloadCatsList(currentListId);
			} else {
				showFlashMessage('error', returnedData.message);
			}
		}
	});
}

function confirmDeleteGift(giftId, listId) {
	currentGiftId = parseInt(giftId);
	currentListId = parseInt(listId);
	$('<div></div>')
	.html(bwLng.confirmGiftDeletion)
	.dialog({
		title: bwLng.confirmation,
		buttons: [
			{
				text: bwLng.deleteIt,
				click: function() { 
					deleteGift(currentGiftId, currentListId);
					$(this).dialog('close');
				}
			},
			{
				text: bwLng.cancel,
				click: function() { 
					$(this).dialog('close');
				}
			}
		]
	});
}

function deleteGift(giftId, listId)
{
	$.ajax({
		type: 'POST',
		url: bwURL + '/a_gifts_mgmt.php?listId=' + listId + '&action=del',
		data: {type: 'gift', id: giftId},
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if(returnedData.status == 'success') {
				showFlashMessage('info', returnedData.message);
				reloadList(listId);
			} else {
				showFlashMessage('error', returnedData.message);
			}
		}
	});
}

function startEditGift(canEdit, giftName, giftId, listId)
{
	if(!canEdit) {
		showFlashMessage('error', bwLng.maxEditsReached);
		return false;
	}
	if(currentGiftId != giftId) {
		currentGiftName = giftName;
	}
	currentGiftId = parseInt(giftId);
	currentListId = parseInt(listId);
	$('<input type="text" id="gift_edit_name" />')
	.insertAfter('#gif_name_' + currentGiftId)
	.val(currentGiftName)
	.focus()
	.blur(function(evt) {
		endEditGift();
	})
	.keyup(function(evt) {
		if(evt.which == 13) {
			endEditGift();
		}
	});
	$('#gif_name_' + currentGiftId).hide();
	$('#actn_edit_gift_' + currentGiftId).hide();
}

function endEditGift()
{
	if(currentGiftId != null) {
		giftName = $('#gift_edit_name').val();
		if(giftName.length < 2) {
			showFlashMessage('error', bwLng.giftNameTooShort);
		} else {
			if(currentGiftName == giftName) {
				// Nothing to change
				$('#gift_edit_name').remove();
				$('#gif_name_' + currentGiftId).show();
				$('#actn_edit_gift_' + currentGiftId).show();
				isEditing = false;
			} else {
				$.ajax({
					type: 'POST',
					url: bwURL + '/a_gifts_mgmt.php?listId=' + currentListId + '&action=edit',
					data: {type: 'gift', catId: currentCatId, id: currentGiftId, newName: giftName},
					dataType: 'json',
					error: function(jqXHR, textStatus, errorThrown) {
						showFlashMessage('error', 'An error occured: ' + errorThrown);
					},
					success: function(returnedData, textStatus, jqXHR) {
						if(returnedData.status == 'success') {
							showFlashMessage('info', returnedData.message);
							$('#gift_edit_name').remove();
							$('#gif_name_' + currentGiftId).text(giftName);
							currentGiftName = giftName;
							$('#gif_name_' + currentGiftId).show();
							$('#actn_edit_gift_' + currentGiftId).show();
						} else {
							showFlashMessage('error', returnedData.message);
							// Nothing to change
							$('#gift_edit_name').remove();
							$('#gif_name_' + currentGiftId).show();
							$('#actn_edit_gift_' + currentGiftId).show();
						}
					}
				});
			}
		}
	}
}

function addGift(listId, detailedAdd, force) {

	catId = $('#gift_cat').val();
	giftData = null;
	detailedAdd = detailedAdd;
	if(catId.length > 0) {
		currentListId = parseInt(listId);
		currentCatId = parseInt(catId);
		if(detailedAdd) {
			//giftName =  
		} else {
			giftName = $('#gift_name').val();
			if(giftName.length < 2) {
				showFlashMessage('error', bwLng.giftNameTooShort);
			} else {
				if(force) {
					giftData = {type: 'gift', catId: currentCatId, name: giftName, force: '1'};
				} else {
					giftData = {type: 'gift', catId: currentCatId, name: giftName, force: '0'};
				}
				$.ajax({
					type: 'POST',
					url: bwURL + '/a_gifts_mgmt.php?listId=' + currentListId + '&action=add',
					data: giftData,
					dataType: 'json',
					error: function(jqXHR, textStatus, errorThrown) {
						showFlashMessage('error', 'An error occured: ' + errorThrown);
					},
					success: function(returnedData, textStatus, jqXHR) {
						if(returnedData.status == 'success') {
							showFlashMessage('info', returnedData.message);
							$('#gift_name').val('');
							$('#section_add_gift').hide();
							reloadList(currentListId);
						} else {
							if(returnedData.status == 'confirm') {
								// Show a confirmation dialog
								$('<div></div>')
								.html(returnedData.message)
								.dialog({
									title: bwLng.confirmation,
									buttons: [
										{
											text: bwLng.addAnyway,
											click: function() { 
												addGift(currentListId, detailedAdd, true);
												$(this).dialog('close');
											}
										},
										{
											text: bwLng.cancel,
											click: function() { 
												$(this).dialog('close');
											}
										}
									]
								});
							} else {
								showFlashMessage('error', returnedData.message);
							}
						}
					}
				});
			}
		}
	}
}

function addSurpriseGift(listId, force) {

	catId = $('#surprise_gift_cat').val();
	giftData = null;
	if(catId.length > 0) {
		currentListId = parseInt(listId);
		currentCatId = parseInt(catId);
		giftName = $('#surprise_gift_name').val();
		if(giftName.length < 2) {
			showFlashMessage('error', bwLng.giftNameTooShort);
		} else {
			if(force) {
				giftData = {type: 'sgift', catId: currentCatId, name: giftName, force: '1'};
			} else {
				giftData = {type: 'sgift', catId: currentCatId, name: giftName, force: '0'};
			}
			$.ajax({
				type: 'POST',
				url: bwURL + '/a_gifts_mgmt.php?listId=' + currentListId + '&action=add',
				data: giftData,
				dataType: 'json',
				error: function(jqXHR, textStatus, errorThrown) {
					showFlashMessage('error', 'An error occured: ' + errorThrown);
				},
				success: function(returnedData, textStatus, jqXHR) {
					if(returnedData.status == 'success') {
						showFlashMessage('info', returnedData.message);
						$('#surprise_gift_name').val('');
						$('#section_add_surprise_gift').hide();
						reloadList(currentListId);
					} else {
						if(returnedData.status == 'confirm') {
							// Show a confirmation dialog
							$('<div></div>')
							.html(returnedData.message)
							.dialog({
								title: bwLng.confirmation,
								buttons: [
									{
										text: bwLng.addAnyway,
										click: function() { 
											addSurpriseGift(currentListId, true);
											$(this).dialog('close');
										}
									},
									{
										text: bwLng.cancel,
										click: function() { 
											$(this).dialog('close');
										}
									}
								]
							});
						} else {
							showFlashMessage('error', returnedData.message);
						}
					}
				}
			});
		}
	}
}

function moveGift(giftId, listId, targetCatId)
{
	var currGiftId = parseInt(giftId);
	var currTargetCatId = parseInt(targetCatId);
	var currListId = parseInt(listId);
	$.ajax({
		type: 'POST',
		url: bwURL + '/a_gifts_mgmt.php?listId=' + listId + '&action=move',
		data: {type: 'gift', targetCatId: currTargetCatId, id: currGiftId},
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if(returnedData.status == 'success') {
				showFlashMessage('info', returnedData.message);
				reloadList(listId);
			} else {
				showFlashMessage('error', returnedData.message);
			}
		}
	});
}

function showBuyWindow(giftName, giftId, listId)
{
	currentGiftId = parseInt(giftId);
	currentListId = parseInt(listId);
	$('#bought_gift_name').text(giftName);
	$('#gift_purchase_dialog').dialog( 'option', 'buttons', 
	[
		{
			text: bwLng.confirmPurchase,
			id: 'btn-confirm-purchase',
			click: function() { 
				$('#btn-confirm-purchase').button( 'disable' );
				$('#btn-confirm-purchase').button( 'option', 'label', bwLng.pleaseWait );
				markGiftAsBought(currentGiftId, currentListId);
			}
		},
		{
			text: bwLng.cancel,
			click: function() {
				$('#gift_purchase_dialog').dialog('close');
			}
		}
	]
	);
	$('#gift_purchase_dialog').dialog( 'open' );
}

function markGiftAsBought(giftId, listId)
{
	$.ajax({
		type: 'POST',
		url: bwURL + '/a_gifts_mgmt.php?listId=' + listId + '&action=mark_bought',
		data: {type: 'gift', id: giftId, purchaseComment: $('#purchase_comment').val()},
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if(returnedData.status == 'success') {
				$('#purchase_comment').val('');
				$('#gift_purchase_dialog').dialog('close');
				showFlashMessage('info', returnedData.message);
				reloadList(listId);
			} else {
				// Reenable the button
				$('#btn-confirm-purchase').button( 'enable' );
				$('#btn-confirm-purchase').button( 'option', 'label', bwLng.confirmPurchase );
				showFlashMessage('error', returnedData.message);
			}
		}
	});
}

function confirmMarkGiftAsReceived(giftId, listId) {
	currentGiftId = parseInt(giftId);
	currentListId = parseInt(listId);
	$('<div></div>')
	.html(bwLng.confirmGiftReceive)
	.dialog({
		title: bwLng.confirmation,
		buttons: [
			{
				text: bwLng.confirm,
				click: function() { 
					markGiftAsReceived(currentGiftId, currentListId);
					$(this).dialog('close');
				}
			},
			{
				text: bwLng.cancel,
				click: function() { 
					$(this).dialog('close');
				}
			}
		]
	});
}

function markGiftAsReceived(giftId, listId)
{
	currentGiftId = parseInt(giftId);
	currentListId = parseInt(listId);
	$.ajax({
		type: 'POST',
		url: bwURL + '/a_gifts_mgmt.php?listId=' + listId + '&action=mark_received',
		data: {type: 'gift', id: giftId},
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if(returnedData.status == 'success') {
				showFlashMessage('info', returnedData.message);
				reloadList(currentListId);
			} else {
				showFlashMessage('error', returnedData.message);
			}
		}
	});
}

function reloadList(listId) {
	currentListId = parseInt(listId);
	$.ajax({
		type: 'GET',
		cache: false,
		url: bwURL + '/a_elem_show.php',
		data: {listId: currentListId, type: 'list'},
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			$('#div_complete_list').effect('fade', 200, function() {setTimeout(function() {
				$( '#div_complete_list' ).removeAttr( 'style' ).hide().fadeIn();
			}, 200 );});
			$('#div_complete_list').html(returnedData);
		}
	});
}

function reloadCatsList(listId) {
	currentListId = parseInt(listId);
	$.ajax({
		type: 'GET',
		cache: false,
		url: bwURL + '/a_elem_show.php',
		data: {listId: currentListId, type: 'cats'},
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if (typeof returnedData !== 'undefined' && returnedData != null) {
				// Replace the categories list content
				var giftCat = $('.gift_cats_list');
				giftCat.find('option').remove();
				for(var i = 0; i < returnedData.length; i++)
				{
					giftCat.append('<option value="' + returnedData[i].id + '">' + returnedData[i].name + '</option>');
				}
				if(returnedData.length > 0)
					giftCat.val(returnedData[0].id);
			}
		}
	});
}

/* Options */

function updatePwd() {
	currentPwd = $('#pass').val();
	newPwd = $('#new_pwd').val();
	newPwdRepeat = $('#new_pwd_repeat').val();
	if(currentPwd.length < 6) {
		showFlashMessage('error', bwLng.currentPwdNotSpecified);
	} else {
		if(newPwd.length < 6 || newPwd != newPwdRepeat) {
			showFlashMessage('error', bwLng.bothRepeatPwdNotMatch);
		} else {
			if(currentPwd == newPwd) {
				showFlashMessage('error', bwLng.nothingChange);
			} else {
				// Let the server handle the rest
				$.ajax({
					type: 'POST',
					cache: false,
					url: bwURL + '/a_opts_mgmt.php?action=editpwd',
					data: {currentPasswd: currentPwd, newPasswd: newPwd, newPasswdRepeat: newPwdRepeat},
					dataType: 'json',
					error: function(jqXHR, textStatus, errorThrown) {
						showFlashMessage('error', 'An error occured: ' + errorThrown);
					},
					success: function(returnedData, textStatus, jqXHR) {
						if(returnedData.status == 'success') {
							showFlashMessage('info', returnedData.message);
							$('#pass').val('');
							$('#new_pwd').val('');
							$('#new_pwd_repeat').val('');
						} else {
							showFlashMessage('error', returnedData.message);
							$('#pass').val('');
							$('#new_pwd').val('');
							$('#new_pwd_repeat').val('');
						}
					}
				});
			}
		}
	}
}

function updateRight(listId, rightElement, rightType) {
	currentListId = parseInt(listId);
	rightElement = rightElement;
	if(rightElement.checked) {
		rElementChecked = true;
		rightData = {rtype: rightType, enabled: '1' };
	} else {
		rElementChecked = false;
		rightData = {rtype: rightType, enabled: '0' };
	}
	$.ajax({
		type: 'POST',
		url: bwURL + '/a_opts_mgmt.php?listId=' + currentListId + '&action=editrights',
		data: rightData,
		dataType: 'json',
		error: function(jqXHR, textStatus, errorThrown) {
			showFlashMessage('error', 'An error occured: ' + errorThrown);
		},
		success: function(returnedData, textStatus, jqXHR) {
			if(returnedData.status == 'success') {
				showFlashMessage('info', returnedData.message);
			} else {
				showFlashMessage('error', returnedData.message);
				// Revert the check status
				if(rElementChecked) {
					rightElement.checked = false;
				} else {
					rightElement.checked = true;
				}
			}
		}
	});
}

