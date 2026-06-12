function gfFormsAddConditionalColumns(version, formId) {
  /*
    The callback function will accept a list of elements, and will cycle
    through identifying and acting on those elements that have the gfmc-column
    class associated to them.

    When that class is found, the objective is to detect if display: none has
    been associated to the elements that it contains. If all children are hidden,
    display: none is applied to the parent gfmc-column element.
  */

  let config = {
    attributes: true,
    attributeFilter: ['style'],
    childList: false,
    subtree: true,
  };

  function callback(mutationList) {
    mutationList.forEach(function(mutation) {
      if (mutation.type === 'attributes') {
        if (
            mutation.target.parentElement &&
            mutation.target.parentElement.parentElement &&
            mutation.target.parentElement.parentElement.className.search('gfmc-column') !== -1
        ) {
          CheckParentsChildrenDisplayValueAndHideParentIfNecessary(
              mutation.target.parentElement.parentElement,
              mutation.target.style.display
          );
        }
      }
    });
  }

  function callback25(mutationList) {
    mutationList.forEach(function(mutation) {
      if (mutation.type === 'attributes') {
        var gfmcParentElement = mutation.target.parentElement;

        if (!gfmcParentElement || !gfmcParentElement.parentElement) {
          return;
        }

        var gfmcColumnWrapper = gfmcParentElement.parentElement;

        if (gfmcParentElement.className.search('gfmc-column') !== -1) {
          CheckParentsChildrenDisplayValueAndHideParentIfNecessary(
              gfmcColumnWrapper,
              mutation.target.style.display
          );
        }
      }
    });
  }

  function CheckParentsChildrenDisplayValueAndHideParentIfNecessary(
      GFMCParent,
      targetDisplayValue
  ) {
    if (!GFMCParent || !GFMCParent.firstChild || !GFMCParent.firstChild.children) {
      return;
    }

    if (GFMCParent.firstChild.children.length === 1) {
      targetDisplayValue !== 'none'
          ? ShowParent(GFMCParent)
          : HideParent(GFMCParent);
    } else if (GFMCParent.firstChild.children.length > 1) {
      if (AreAllChildrenHidden(GFMCParent)) {
        HideParent(GFMCParent);
      } else {
        ShowParent(GFMCParent);
      }
    }
  }

  function HideParent(node) {
    node.style.display = 'none';
  }

  function ShowParent(node) {
    node.style.removeProperty('display');
  }

  function AreAllChildrenHidden(node) {
    let childrenHiddenFlag = true;

    if (!node || !node.firstChild || !node.firstChild.children) {
      return false;
    }

    for (let child of node.firstChild.children) {
      if (child.style !== 'undefined') {
        child.style.display !== 'none' ? childrenHiddenFlag = false : null;
      }
    }

    return childrenHiddenFlag;
  }

  let formWrapper = document.getElementById('gform_wrapper_' + formId);

  if (!formWrapper) {
    return;
  }

  let gravityFormPages = formWrapper.getElementsByClassName('gform_page');
  let observer = (version >= 2.5)
      ? new MutationObserver(callback25)
      : new MutationObserver(callback);

  if (gravityFormPages.length > 0) {
    for (let i = 0; i < gravityFormPages.length; i++) {
      observer.observe(gravityFormPages[i], config);
    }
  } else {
    let gravityForm = formWrapper.querySelector('.gform_body');

    if (gravityForm) {
      observer.observe(gravityForm, config);
    }
  }
}