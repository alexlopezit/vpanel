{include file="header.html.tpl" ansicht="Benutzerverwaltung" menupunkt="user"}
<div class="buttonbox">
	<a href="{"users_create"|___}" class="btn btn-success">{"Neue*n Benutzer*in anlegen"|__}</a>
</div>
{if count(users) > 0}
	{include file="userlist.block.tpl" users=$users}
	{if count(users) > 10}
		<div class="buttonbox">
			<a href="{"users_create"|___}" class="btn btn-success">{"Neue*n Benutzer*in anlegen"|__}</a>
		</div>
	{/if}
{/if}
{include file="footer.html.tpl"}
