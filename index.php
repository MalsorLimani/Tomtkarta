<!--

	/*
	*** TOMTKARTA SOM PDF ***
	*** UTEVECKLAT AV MALSOR LIMANI, STADSBYGGNADSKONTORET, JÖNKÖPINGS KOMMUN
	*** @malsor, malsor.limani@jonkoping.se
	*** Licens: CC-BY-NC-SA - https://creativecommons.org/licenses/by-nc-sa/4.0/
    ***
	***/

-->
<html>
<script>
if(window.location.protocol != 'https:') {
  location.href = location.href.replace("http://", "https://");
}
</script>
<script src="jquery-2.1.1.min.js" type="application/javascript"></script>
<script src="jquery-ui.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, shrink-to-fit=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link href="favicon.png" rel="shortcut icon" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="stil.css">
<div class="sv-text-portlet-content">
	<form method="POST" id="form" action="get.php" target="_blank" style="text-align:center;">
		<h1 class="heading" style="margin: 0px -11px -11px; padding: 15px 10px;">
			Skapa tomtkarta
		</h1>
		<p><br>Fyll i din adress nedan<br><br>
			<span><input id="sokinput" name="adr" type="text"></span><br><br>
		</p>
		<input id="sokgata" name="gata" type="hidden">
		<input id="sokcity" name="city" type="hidden">
	</form>
</div>
<p id="resultat"></p>
<script>
	$("#sokinput").val("");
	$( "#sokinput" ).autocomplete({
		minLength: 3,
		source: function( request, response ) {
			$.ajax({
				url: "sok_adr.php",
				dataType: "json",
				data: {
					adr: $("#sokinput").val()
				},
				success: function( e ) {
					availableAdr =  new Array;
					$.each(e, function(d){
						var gata = this.split(";")[0];
						var city = this.split(";")[1];
						var postcode = this.split(";")[3];
						var adr = gata+ ", " +postcode + " " + city;
						availableAdr.push({"label":adr,"gata":gata,"city":city});
					})
					response( availableAdr );
				}
			});
		},
		autoFocus: true,
		select: function( event, ui ) {
			var gata = ui.item.gata
			var city = ui.item.city
			
			setTimeout(function(){ $("#sokinput").val(ui.item.label); }, 10);
			$("#sokgata").val(gata);
			$("#sokcity").val(city);
			//$("#form").submit();
			$("#resultat").html("Skapar PDF...");
			$.ajax({
				type: "POST",
				url: "get.php",
				data: {
					"gata":gata,
					"city":city
				},
				success: function(d) {
					var url = d.results[0].value.url;
					var fast = d.messages.fastbet;
					$("#resultat").html("<img id='pdfimg' src='pdf_stor.png'> <a href='"+url+"' target='_blank'> Ladda ner PDF för fastigheten "+fast+"</a><br>");
					$("#sokinput").val("");
				},
				error: function(d){
					var result = JSON.parse(d.responseText);
					var fast = result.messages[0].fastbet;
					switch(d.status) {
						case 620:
							$("#resultat").html("<p style='color:red;'>"+result.messages[0].error+"</p>");
							$("#arende").slideDown();
							console.info("Området för stort, skicka till e-tjänst");
							break;
						default:
							$("#resultat").html("<p style='color:red;'>"+result.messages[0].error+"</p>");
					} 
				}
			});
		},
		response: function( event, ui ) {
			$(".sokx").fadeIn();	
		},
		close: function( event, ui ) {
			if (!$("#sokinput").val()) {
				empty();
			}
		}
	});
	
	$("button[name=reset]").on("click", function(){
		$('#formarende').trigger("reset");
	});
</script>
<link rel="stylesheet" href="jquery-ui.css">
<link href="//fonts.googleapis.com/css?family=Open+Sans:400,700" media="screen,projection" rel="stylesheet" type="text/css">
<div id="licens"><a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons-licens" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/4.0/80x15.png" /></a><br />Utvecklad med delningsglädje av ML, Jönköpings kommun.</div>
</html>