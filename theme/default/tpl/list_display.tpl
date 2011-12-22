{foreach from=$list->getCategories() item=category}
	{if $category->giftsCount > 0}
			<div id="cat_{$category->getId()}" class="category_list_element">
				{if $sessionOk && $user->canDoActionForList($list->getId(), 'edit')}
					<a href="/" onclick="deleteCat({$list->getId()}, {$category->getId()}); return false" title="{$lngDelete}"><img alt="{$lngDelete}" class="icon_text" src="{$themeWebDir}/img/delete.png" /></a> 
				{/if}
				<span class="category_name">{$category->name|ucfirst} :</span>
				{foreach from=$category->getGifts() item=gift}
					<div id="gift_list_elem_{$gift->getId()}" class="gift_list_element">
						{if $sessionOk}
							{if $user->canDoActionForList($list->getId(), 'edit')}
							<a href="/" onclick="deleteGift(); return false" title="{$lngDelete}"><img alt="{$lngDelete}" class="icon_text" src="{$themeWebDir}/img/delete.png" /></a>&nbsp;
								{if $cfgMaxEdits && $gift->editsCount == $cfgMaxEdits}
									<a href="/" onclick="editGift(false); return false" title="{$lngCannotEdit}"><img alt="{$lngEdit}" class="icon_text" src="{$themeWebDir}/img/edit_not.png" /></a>&nbsp;
								{else}
									<a href="/" onclick="editGift(true); return false" title="{$lngCannotEdit}"><img alt="{$lngEdit}" class="icon_text" src="{$themeWebDir}/img/edit.png" /></a>&nbsp;
								{/if}
							{/if}
							<span id="gif_name_{$gift->getId()}" class="gift_name{if $gift->isBought && !$user->isListOwner($list)} bought_gift{/if}" onclick="showGiftDetailsWindow({$gift|json_encode|escape})">{$gift->name|ucfirst}</span>
							{if $user->canDoActionForList($list->getId(), 'mark') && !$gift->isBought}
							&nbsp;<a href="/" onclick="markGiftAsBought(); return false" title="{$lngMarkAsBought}"><img alt="{$lngMarkAsBought}" class="icon_text" src="{$themeWebDir}/img/gift_buy.png" /></a> 
							{/if}
							{if $user->isListOwner($list) && !$gift->isReceived}
							&nbsp;<a href="/" onclick="markGiftAsReceived(); return false" title="{$lngMarkAsReceived}"><img alt="{$lngMarkAsReceived}" class="icon_text" src="{$themeWebDir}/img/gift_received.png" /></a> 
							{/if}
							{if $gift->isBought && !$user->isListOwner($list)}
								{if !empty($gift->boughtComment)}
								<img class="icon_text gift_status" alt="comment" title="has comment" src="{$themeWebDir}/img/comment.png" />
								{/if}
								<img class="icon_text gift_status" alt="bought" title="is bought" src="{$themeWebDir}/img/gift_bought.png" />
							{/if}
							{if $gift->isSurprise}
								<img class="icon_text gift_status" alt="surprise" title="is surprise" src="{$themeWebDir}/img/surprise.png" />
							{/if}
						{else}
							<span id="gif_name_{$gift->getId()}" class="gift_name" onclick="showGiftDetailsWindow({$gift|json_encode|escape})">{$gift->name|ucfirst}</span>
						{/if}
					</div>
				{/foreach}
			</div>
	{else}
		{if $sessionOk && $user->canDoActionForList($list->getId(), 'edit')}
			<div id="cat_{$category->getId()}" class="category_list_element">
				<a href="/" onclick="deleteCat({$list->getId()}, {$category->getId()}); return false" title="{$lngDelete}"><img alt="{$lngDelete}" class="icon_text" src="{$themeWebDir}/img/delete.png" /></a> 
				<span class="category_name">{$category->name|ucfirst} :</span>
			</div>
		{/if}
	{/if}
{foreachelse}
	<i>{$lngInfoEmptyList}</i>
{/foreach}
{if $sessionOk}
{/if}