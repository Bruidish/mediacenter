/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

/** Model => FileModel.php */
var FileModel = Backbone.Model.extend({
  urlRoot: `/file`,
  defaults: {
    title: "",
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
    app.files.bind("reset add remove", () => this.render());
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

  initialize: function () {
    this.model.bind("sync", () => this.render());
  },

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
    'click .remove-archive': 'removeArchive',
    'click .move-left': 'previousModel',
    'click .move-right': 'nextModel',
  },

  /** Ferme la modal
   * If modal have been opened with a new model
   * And is closed without saving data
   * The new model is removed from main collection
   */
  closeModal: function () {
    if (typeof this.model.id == 'undefined' && this.model.attributes.title == '') {
      app.files.remove(this.model)
    }

    this.$el.parent().removeClass('active')
    this.model.trigger('modal:close')
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
        .then(() => this.model.trigger('change'));
    }
  },

  /** Supprime un fichier vidéo mais conserve en base les données enregistrées pour ce titre
   *  @todo lorsque l'on supprime un fichier, il faut mettre à jour la vue sans  la retirer de la collection
  */
  removeFile: function () {
    if (confirm(`Souhaitez vous supprimer définitivement le fichier ${this.model.attributes.filename} ?`)) {
      $.ajax({
        url: `/file/delete`,
        type: 'POST',
        data: {
          path: this.model.attributes.path
        },
        success: () => {
          this.model.set({ extension: null }).save();
        }
      })
    }
  },

  /** Supprime les données enregistrées en base */
  removeArchive: function () {
    if (confirm(`Souhaitez vous supprimer définitivement toutes les données de "${this.model.attributes.title}" ?`)) {
      $.ajax({
        url: `/archive/delete`,
        type: 'POST',
        data: {
          id: this.model.id
        },
        success: () => {
          app.files.remove(this.model)
        }
      })
    }
  },

  /** Recharge la modal avec le FileModel précédent */
  previousModel: function () {
    let index = app.files.indexOf(this.model);
    while (true != false) {
      index--;
      if (index < 0) {
        index = app.files.length - 1;
      }

      if (app.files.at(index).get('hidden') !== true) {
        return $('#modalWrap').html(new FileModalView({ model: app.files.at(index) }).render().el);
      }
    }
  },

  /** Recharge la modal avec le FileModel suivant */
  nextModel: function () {
    let index = app.files.indexOf(this.model);
    while (true != false) {
      index++;
      if (index == app.files.length) {
        index = 0;
      }

      if (app.files.at(index).get('hidden') !== true) {
        return $('#modalWrap').html(new FileModalView({ model: app.files.at(index) }).render().el);
      }
    }
  }
});
