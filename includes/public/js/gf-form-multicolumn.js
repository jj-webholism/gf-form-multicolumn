function gfFormsAddConditionalColumns () {
  var gfieldCollection = document.getElementsByClassName('gfield')
  for (var i = 0; i < gfieldCollection.length; i++) {
    var config = { attributes: true, childList: true, subtree: true }
    
    const callback = function (mutationsList, observer) {
      for (var j = 0; j < mutationsList.length; j++) {
        var gfmcWrapper = mutationsList[j].target.parentElement.parentElement
        if (gfmcWrapper.className.indexOf('gfmc-column') === 0) {
          if (mutationsList[j].target.attributes['style'].value === 'display: none;') {
            gfmcWrapper.style.display = 'none'
          } else {
            gfmcWrapper.style.removeProperty('display')
          }
        }
      }
    }
    var observer = new MutationObserver(callback)
    observer.observe(gfieldCollection[i], config)
  }
}
