"use strict";

var AsproUI = AsproUI || {};

AsproUI.AnimateEasy = function ()
{

};

AsproUI.AnimateEasy.slideDown = function (node, fnCallback, duration, bxTransition)
{
  let element = this._getNodeElement(node);

  if (!(element instanceof HTMLElement)) {
    return;
  }

  if (element.offsetHeight != 0) {
    return;
  }

  element.style.display = 'block';
  element.style.removeProperty('height');

  let height = element.offsetHeight;
  element.style.height = 0;

  let easing = new BX.easing({
    duration: duration ? duration : 300,
    start: {
      height: 0
    },
    finish: {
      height: height
    },
    transition: bxTransition ? bxTransition : BX.easing.transitions.linear,
    step: function (state)
    {
      element.style.height = state.height + 'px';
    },
    complete: function ()
    {
      element.setAttribute('data-animate-state', 'slide-down-complite');

      if (typeof fnCallback != 'function') {
        return;
      }

      fnCallback('slide-down', true);
    }
  });

  easing.animate();
}

AsproUI.AnimateEasy.slideUp = function (node, fnCallback, duration, bxTransition)
{
  let element = this._getNodeElement(node);

  if (!(element instanceof HTMLElement)) {
    return;
  }

  if (element.offsetHeight == 0) {
    return;
  }

  let height = element.offsetHeight;

  let easing = new BX.easing({
    duration: duration ? duration : 300,
    start: {
      height: height
    },
    finish: {
      height: 0
    },
    transition: bxTransition ? bxTransition : BX.easing.transitions.linear,
    step: function (state)
    {
      element.style.height = state.height + 'px';
    },
    complete: function ()
    {
      element.setAttribute('data-animate-state', 'slide-up-complite');
      element.style.display = 'none';

      if (typeof fnCallback != 'function') {
        return;
      }

      fnCallback('slide-up', false);
    }
  });

  easing.animate();
}

AsproUI.AnimateEasy.slideToggle = function (node, fnCallback, duration, bxTransition)
{
  let element = this._getNodeElement(node);

  if (!(element instanceof HTMLElement)) {
    return;
  }

  if (element.offsetHeight == 0) {
    this.slideDown(node, fnCallback, duration, bxTransition);
  } else {
    this.slideUp(node, fnCallback, duration, bxTransition);
  }
}

AsproUI.AnimateEasy._getNodeElement = function (node)
{
  let element;

  if (node instanceof HTMLElement) {
    element = node;
  } else {
    element = document.getElementById(node);
  }

  return element;
}
