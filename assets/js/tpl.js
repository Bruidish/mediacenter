/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

window.Tpl = {
  /** @var string stocke les templates utilisées */
  tplCached: {},

  /** Retourne la template passées en argument après l'avoir mise en cache
   * Utilise celle en cache s'il y en a déjà une
   *
   * @param string
   * @param string
   * @param array
   *
   * @return string
  */
  display: function (tplDir, tplName, tplData) {
    if (!this.tplCached[tplName]) {
      var that = this;
      $.ajax({
        url: `${tplDir}${tplName}.html`,
        method: 'GET',
        async: false,
        success: function (response) {
          that.create(tplName, response);
        }
      });
    }
    return this.tplCached[tplName](tplData);
  },

  /** Créait un template underscore
   *
   * @param string tplName
   * @param string tplHtml
   */
  create: function (tplName, tplHtml) {
    this.tplCached[tplName] = _.template(tplHtml)
  },

};
