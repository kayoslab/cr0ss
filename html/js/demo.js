/**
 * Particleground demo
 * @author Jonathan Nicol - @mrjnicol
 */

// This can be used to set the Particles Effects. Check README for more details!
document.addEventListener('DOMContentLoaded', function () {
  particleground(
    document.getElementById('particles'),
    {
      dotColor: '#eca139',
      lineColor: '#eca139',
      minSpeedX: 0.5,
      maxSpeedX: 1,
      minSpeedY: 0.5,
      maxSpeedY: 1,
      density: 9000,
      parallaxMultiplier: 4.5
    }
  );
  var intro = document.getElementById('intro');
  intro.style.marginTop = - intro.offsetHeight / 2 + 'px';
}, false);
