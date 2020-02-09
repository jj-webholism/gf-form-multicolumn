let all = document.getElementsByClassName('gfield')

for (var i = 0; i < all.length; i++) {
  let config = { attributes: true, childList: true, subtree: true }

  let callback = function (mutationsList, observer) {
    for (var j = 0; j < mutationsList.length; j++) {
      let gfmcWrapper = mutationsList[j].target.parentElement.parentElement
      if (gfmcWrapper.className.indexOf('gfmc-column') === 0) {
        if (mutationsList[j].target.attributes['style'].value === 'display: none;') {
          gfmcWrapper.style.display = 'none'
        } else {
          gfmcWrapper.style.removeProperty('display')
        }
      }
    }
  }
  let observer = new MutationObserver(callback)
  observer.observe(all[i], config)
}
