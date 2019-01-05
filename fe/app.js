//app.js
const service = require('/utils/service');
const config = require('/utils/config');
App({
  onLaunch: function (r) {
    service.userConfig(this);
  },
  onShow: function (r) {
    this.globalData.wx_config = r || {};
  },
  appShare: () => {
    return {
      path: '/pages/index/index'
    }
  },
  globalData: {
    app_init: false,
    is_login: false,
    profile: null,
    user_config: {},
    wx_config: {},
  }
})