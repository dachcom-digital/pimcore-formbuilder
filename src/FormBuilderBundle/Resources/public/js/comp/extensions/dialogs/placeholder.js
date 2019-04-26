'use strict';

CKEDITOR.dialog.add( 'form_mail_editor_placeholder', function( editor ) {
	var generalLabel = editor.lang.common.generalTab,
		validNameRegex = /^[^\[\]<>]+$/;

	return {
		title: 'Configuration',
		minWidth: 300,
		minHeight: 80,
		contents: [
			{
				id: 'info',
				label: generalLabel,
				title: generalLabel,
				elements: [
					{
						id: 'name',
						type: 'text',
						style: 'width: 100%;',
						label: 'Format',
						'default': '',
						required: true,
						validate: CKEDITOR.dialog.validate.regex( validNameRegex, 'invalid' ),
						setup: function( widget ) {
							this.setValue( widget.data.name );
						},
						commit: function( widget ) {
							widget.setData( 'name', this.getValue() );
						}
					}
				]
			}
		]
	};
} );