// store/index.js
window.store = new Vuex.Store({
  modules: {
    user: window.userModule,
    cart: window.cartModule
  }
})