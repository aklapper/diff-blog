!function(t){var e={};function n(r){if(e[r])return e[r].exports;var o=e[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=t,n.c=e,n.d=function(t,e,r){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(r,o,function(e){return t[e]}.bind(null,o));return r},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=15)}([function(t,e){!function(){t.exports=this.wp.domReady}()},,function(t,e,n){var r=n(5),o=n(6),i=n(7),a=n(8);t.exports=function(t){return r(t)||o(t)||i(t)||a()}},,function(t,e){t.exports=function(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=new Array(e);n<e;n++)r[n]=t[n];return r}},function(t,e,n){var r=n(4);t.exports=function(t){if(Array.isArray(t))return r(t)}},function(t,e){t.exports=function(t){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t))return Array.from(t)}},function(t,e,n){var r=n(4);t.exports=function(t,e){if(t){if("string"==typeof t)return r(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?r(t,e):void 0}}},function(t,e){t.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}},function(t,e){t.exports=function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}},function(t,e){function n(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}t.exports=function(t,e,r){return e&&n(t.prototype,e),r&&n(t,r),t}},function(t,e){t.exports=function(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}},,,,function(t,e,n){"use strict";n.r(e);var r=n(2),o=n.n(r),i=n(9),a=n.n(i),u=n(10),l=n.n(u),c=n(11),s=n.n(c),f=n(0),d=n.n(f);function p(t,e){var n;if("undefined"==typeof Symbol||null==t[Symbol.iterator]){if(Array.isArray(t)||(n=function(t,e){if(!t)return;if("string"==typeof t)return y(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);"Object"===n&&t.constructor&&(n=t.constructor.name);if("Map"===n||"Set"===n)return Array.from(t);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return y(t,e)}(t))||e&&t&&"number"==typeof t.length){n&&(t=n);var r=0,o=function(){};return{s:o,n:function(){return r>=t.length?{done:!0}:{done:!1,value:t[r++]}},e:function(t){throw t},f:o}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var i,a=!0,u=!1;return{s:function(){n=t[Symbol.iterator]()},n:function(){var t=n.next();return a=t.done,t},e:function(t){u=!0,i=t},f:function(){try{a||null==n.return||n.return()}finally{if(u)throw i}}}}function y(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=new Array(e);n<e;n++)r[n]=t[n];return r}var h=function(){function t(e,n){var r=this;a()(this,t),s()(this,"toggle",(function(t){r.tr.classList.contains("expanded")?r.onClose(t):r.onOpen(t)})),this.tr=e,this.index=n,this.tr.classList.add(this.index%2?"odd":"even")}return l()(t,[{key:"init",value:function(){var t=this;(this.details=this.tr.querySelector(".column-details details"),this.details)&&(this.createNewTr(),[].concat(o()(this.tr.querySelectorAll(".single-url-detail-toggle")),[this.details.querySelector("summary")]).forEach((function(e){e.addEventListener("click",(function(){t.toggle(e)}))})))}},{key:"createNewTr",value:function(){this.newTr=document.createElement("tr"),this.newTr.classList.add("details"),this.newTr.classList.add(this.index%2?"odd":"even");var t=document.createElement("td");t.setAttribute("colspan",this.getRowColspan());var e,n=p(this.details.childNodes);try{for(n.s();!(e=n.n()).done;){var r=e.value;"SUMMARY"!==r.tagName&&t.appendChild(r.cloneNode(!0))}}catch(t){n.e(t)}finally{n.f()}this.newTr.appendChild(t)}},{key:"getRowColspan",value:function(){return o()(this.tr.childNodes).filter((function(t){return["TD","TH"].includes(t.tagName)})).length}},{key:"onOpen",value:function(t){this.tr.parentNode.insertBefore(this.newTr,this.tr.nextSibling),this.tr.classList.add("expanded"),"SUMMARY"!==t.tagName&&this.details.setAttribute("open",!0)}},{key:"onClose",value:function(t){this.tr.parentNode.removeChild(this.newTr),this.tr.classList.remove("expanded"),"SUMMARY"!==t.tagName&&this.details.removeAttribute("open")}}]),t}(),v=function(){function t(){a()(this,t),this.rows=o()(document.querySelectorAll('.wp-list-table tr[id^="tag-"]')).map((function(t,e){var n=new h(t,e);return n.init(),n})).filter((function(t){return t.details}))}return l()(t,[{key:"init",value:function(){this.addToggleAllListener()}},{key:"addToggleAllListener",value:function(){var t=this,e=!1,n=o()(document.querySelectorAll(".column-details button.error-details-toggle"));window.addEventListener("click",(function(r){var o;n.includes(r.target)&&(o=r.target,e=!e,t.rows.forEach((function(t){e?t.onOpen(o):t.onClose(o)})))}))}}]),t}();d()((function(){(new v).init()}))}]);