// store/cart.js
window.cartModule = {
  namespaced: true,
  state: {
    productos: []
  },
  mutations: {
    agregarProducto(state, producto) {
      state.productos.push(producto)
    }
  }
}
