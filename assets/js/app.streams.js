/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

/** Model => FileModel.php */
var FileModel = Backbone.Model.extend({
  urlRoot: `/file`,
  defaults: {
    image: ""
  }
});

/** Collection => FileModel.php */
var FileCollection = Backbone.Collection.extend({
  model: FileModel,
  url: `/files`,
  comparator: (model) => `${model.get('title')} ${model.get('release_year')}`
});



/** View => Listes lesfichiers dans le main */
var FileListView = Backbone.View.extend({
  id: 'FileListView',
  el: 'body > main',

  initialize: function () {
    app.files.bind("change reset add remove", () => this.render());
  },

  render: function () {
    this.$el.empty();

    app.files.map(model => {
      if (!model.get('hidden')) {
        this.$el.append(new FileView({
          model: model
        }).render().el)
      }
    });
  }
});


/** View => affiche un fichier dans le main */
var FileView = Backbone.View.extend({
  tagName: 'article',

  render: function () {
    this.$el
      .html(Tpl.display('assets/views/', 'file', {
        model: this.model.attributes,
      }));

    return this;
  },

  events: {
    'click': 'openModal'
  },

  openModal: function () {
    $('#modalWrap')
      .removeClass('active')
      .html(new FileModalView({ model: this.model }).render().el);
    setTimeout(() => $('#modalWrap').addClass('active'), 100);
  }
});


/** View => affiche les détails d'un fichier dans la modal */
var FileModalView = Backbone.View.extend({
  initialize: function () {
    this.model.bind("change add", () => this.render());
    this.model.bind("remove", () => this.closeModal());
  },
  render: function () {
    this.$el
      .html(Tpl.display('assets/views/', 'file.modal', {
        model: this.model.attributes,
      }));
    return this;
  },

  events: {
    'click overlay': 'closeModal',
    'change input': 'editModel',
    'change textarea': 'editModel',
    'click .toggle-filedetails': 'toggleFileDetails',
    'click .rename-file': 'renameFile',
    'click .encode-file': 'encodeFile',
    'click .remove-file': 'removeFile',
    'click .move-left': 'previousModel',
    'click .move-right': 'nextModel',
  },

  /** Ferme la modal */
  closeModal: function () {
    this.$el.parent().removeClass('active')
  },

  /** Enregistre les nouvelles données d'un FileModel */
  editModel: function (event) {
    this.model.set(event.target.name, event.target.value).save();
  },

  /** Ouvre le fichier vidéo dans le navigateur */
  openFile: function () {
    window.location = this.model.attributes.path;
  },

  /** Affiche / masque les détails du fichier dans la modal */
  toggleFileDetails: function (event) {
    $(event.target).toggleClass(event.target.dataset.iconstart).toggleClass(event.target.dataset.iconend)
    this.$el.toggleClass('aside-active')
  },

  /** Normalise le nom du fichier vidéo à partir d'une nommenclature utilisant les données saisies dans le formulaire */
  renameFile: function () {
    if (confirm(`Renommer le fichier à partir des données du film ?`)) {
      $.post(`/file/rename`, { path: this.model.attributes.path })
        .then(response => {
          this.model.set({ filename: response.filename })
        });
    }
  },

  /** Encode le fichier au format mp4
   *
   * @see ffmpeg
   * @todo pose des problèmes de qualité
   *
   */
  encodeFile: function () {
    if (confirm(`Renommer et encoder définitivement le fichier au format mp4 ? (béta)`)) {
      $.post(`/file/encode`, { path: this.model.attributes.path })
        .then(response => this.model.trigger('change'));
    }
  },

  /** Supprime un fichier vidéo mais conserve en base les données enregistrées pour ce titre */
  removeFile: function () {
    if (confirm(`Souhaitez vous supprimer définitivement le fichier ${this.model.attributes.filename} ?`)) {
      $.ajax({
        url: `/file/delete`,
        type: 'POST',
        data: {
          path: this.model.attributes.path
        },
        success: repsonse => {
          app.files.remove(this.model)
        }
      })
    }
  },

  /** Recharge la modal avec le FileModel précédent
   * @todo se base sur toute la collection au lieu de tenir compte de la collection visible
   */
  previousModel: function () {
    let index = app.files.indexOf(this.model) - 1;
    if (index < 0) {
      index = app.files.length - 1;
    }
    $('#modalWrap').html(new FileModalView({ model: app.files.at(index) }).render().el);
  },

  /** Recharge la modal avec le FileModel suivant
   * @todo se base sur toute la collection au lieu de tenir compte de la collection visible
   */
  nextModel: function () {
    let index = app.files.indexOf(this.model) + 1;
    if (index == app.files.length) {
      index = 0;
    }
    $('#modalWrap').html(new FileModalView({ model: app.files.at(index) }).render().el);
  }
});
