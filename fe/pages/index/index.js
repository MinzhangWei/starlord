//index.js
const service = require('../../utils/service');
const app = getApp();
let self;
Page({
  data: {
    notice_list: [],
    list: [],
    loading: false
  },
  onLoad: function (r) {
    self = this;
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    const { user_config } = app.globalData;
    if (user_config && user_config.docoment) {
      this.setData({
        notice_list: user_config.docoment.notice_list
      });
    }
    this.onLoadData();
  },
  onPullDownRefresh: function () {
    // setTimeout(wx.stopPullDownRefresh, 2000);
    this.onLoadData();
  },

  onLoadData: () => {
    self.setData({ loading: true});
    service.getGroupListByUserId((success, data) => {
      if (success) {
        self.setData({
          list: data,
          loading: false
        });
      } else {
        self.setData({ loading: false });
      }

      wx.stopPullDownRefresh();
    });
  }
})
