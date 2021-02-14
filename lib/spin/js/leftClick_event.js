// Actions au clic ------------------------------------------------
(function($)
{
	$(function()
	{
		// Chargement du texte
		$('.spin').on('click', '.spin_first_text', function() {
			var recup_node	 	= this;
			var container_id 	= this.parentNode.id;

			envoi_master_spin(recup_node, container_id);
		});

		// Fermeture d'un menu contextuel
		$('body').click( function(event)
		{
			// Texte
			if ($('.cm').length > 0 && $(event.target).parents('.cm').length == 0) {
				var elem	= $('#' + $('.cm').attr('idnode'));				// Récupération noeud du block texte
				cm_close( $(elem).attr('id') );								// Fermeture du menu et remise en ordre des block textes
			}

			// Synonymes
			if ($('.cm_synonymes').length > 0 && $(event.target).parents('.cm_synonymes').length == 0) {
				var elem	= $('#' + $('.cm_synonymes').attr('idnode'));	// Récupération noeud du block synonymes
				cm_close( $(elem).attr('id') );								// Fermeture du menu et remise en ordre des block synonymes
			}

			// Spin
			if ($('.cm_spin').length > 0 && $(event.target).parents('.cm_spin').length == 0) {
				var elem	= $('#' + $('.cm_spin').attr('idnode'));		// Récupération noeud du block spin
				cm_spin_close( $(elem).attr('id') );						// Fermeture du menu et remise en ordre des block spins
			}

			// Tag
			if ($('.cm_tag').length > 0 && $(event.target).parents('.cm_tag').length == 0) {
				var elem	= $('#' + $('.cm_tag').attr('idnode'));			// Récupération noeud tag
				cm_tag_close( $(elem).attr('id') );							// Fermeture du menu et remise en ordre des tags
			}
		});

		// Action au click
		$('.spin').on('click', function()
		{
			// Ferme toutes les actions en cours des autres spin
			initOtherSpin(this);
		});

		// Action au click sur un mot
		$('.spin').on('click', '.off, .on', function()
		{
			/**
			 * Dans le cas d'un spin calculé, on désactive la sélection
			 * s'il est en statut "calcul_on" ou "calcul_end"
			 */
			if (checkStatutSpinCalc(this)) {
				selectText(this);
			}
		});

		// Action au click sur un bloc de spin
		$('.spin').on('click', '.spin_off', function(event)
		{
			var posX = recupPosX(this, event);
			modal_editSpin(this, posX);
		});

		// Action au click sur un bloc de spin
		$('.spin').on('click', '.spin_on', function(event)
		{
			closeSpinMajFirstComb(this);
		});

		// Action au click pour supprimer une combo d'un spin
		$('.spin').on('click', '.combo_erase', function() {
			// combo_erase(this);
		});

		// Action au click pour un lien d'ancre
		$('.spin').on('click', '.link', function() {
			// link_modal(this);
			// remove_tools_master_spin();
		});
	});
})(jQuery);
// ----------------------------------------------------------------
