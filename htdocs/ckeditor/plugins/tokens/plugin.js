CKEDITOR.plugins.add('tokens',
    {
        requires: ['richcombo'], //, 'styles' ],
        init: function (editor) {
            var config = editor.config,
                lang = editor.lang.format;

            // Gets the list of tags from the settings.
            var tags = []; //new Array();
            //this.add('value', 'drop_text', 'drop_label');
            // tags[0] = ["[contact_name]", "Name", "Name"];
            // tags[1] = ["[contact_email]", "email", "email"];
            // tags[2] = ["[contact_user_name]", "User name", "User name"];

            if (editor.config.tags) {
                tags = editor.config.tags;
            }

            // Create style objects for all defined styles.

            editor.ui.addRichCombo('tokens',
                {
                    label: "Insert tokens",
                    title: "Insert tokens",
                    voiceLabel: "Insert tokens",
                    className: 'cke_format',
                    multiSelect: false,

                    panel:
                        {
                            css: [config.contentsCss, CKEDITOR.getUrl(CKEDITOR.skin.getPath('editor') + 'editor.css')],
                            voiceLabel: lang.panelVoiceLabel
                        },

                    init: function () {
                        this.startGroup("Tokens");
                        //this.add('value', 'drop_text', 'drop_label');
                        for (var this_tag in tags) {
                            this.add("[%" + tags[this_tag] + "%]", tags[this_tag], tags[this_tag]);
                        }
                    },

                    onClick: function (value) {
                        editor.focus();
                        editor.fire('saveSnapshot');
                        editor.insertHtml(value);
                        editor.fire('saveSnapshot');
                    }
                });
        }
    });
