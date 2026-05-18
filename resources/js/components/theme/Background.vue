<script setup lang="ts">
import { useBackground } from '@/composables/useBackground'

const {
  speed,
  dots,
  interactive,
  gradientBg,
  gSize
} = useBackground()
</script>

<template>
  <div
    class="gradient-bg-wrapper"
    :class="{ 'gradient-bg': gradientBg }"
  >
    <svg xmlns="http://www.w3.org/2000/svg">
      <defs>
        <filter id="goo">
          <feGaussianBlur
            in="SourceGraphic"
            stdDeviation="10"
            result="blur"
          />
          <feColorMatrix
            in="blur"
            mode="matrix"
            values="1 0 0 0 0
                    0 1 0 0 0
                    0 0 1 0 0
                    0 0 0 18 -8"
            result="goo"
          />
          <feBlend
            in="SourceGraphic"
            in2="goo"
          />
        </filter>
      </defs>
    </svg>

    <div class="gradients-container">
      <div
        v-show="dots"
        class="g1"
      />
      <div
        v-show="dots"
        class="g2"
      />
      <div
        v-show="dots"
        class="g3"
      />
      <div
        v-show="dots"
        class="g4"
      />
      <div
        v-show="dots"
        class="g5"
      />
      <div
        v-if="interactive"
        class="interactive"
      />
    </div>
  </div>
</template>

<style>
:root {
  --color-bg1: rgb(108, 0, 162, 0.05);
  --color-bg2: rgb(0, 17, 82, 0.2);
  --color1: 18, 113, 255;
  --color2: 221, 74, 255;
  --color3: 100, 220, 255;
  --color4: 200, 50, 50;
  --color5: 180, 180, 50;
  --color-interactive: 140, 100, 255;
  --circle-size: 80%;
  --blending: hard-light;
}

.gradient-bg-wrapper {
  position: fixed;
  inset: 0;
  overflow: hidden;
  z-index: -1;
}

.gradient-bg {
  background: linear-gradient(250deg, var(--color-bg1), var(--color-bg2));
}

.gradient-bg svg {
  position: fixed;
  width: 0;
  height: 0;
}

.gradients-container {
  width: 100%;
  height: 100%;
  filter: url(#goo) blur(40px);
}

.g1 {
  animation: moveVertical calc(v-bind(speed) * 3s) ease infinite;
}
.g2 {
  animation: moveInCircle calc(v-bind(speed) * 2s) reverse infinite;
}
.g3 {
  animation: moveInCircle calc(v-bind(speed) * 4s) linear infinite;
}
.g4 {
  animation: moveHorizontal calc(v-bind(speed) * 4s) ease infinite;
}
.g5 {
  animation: moveInCircle calc(v-bind(speed) * 2s) ease infinite;
}

.g1, .g2, .g3, .g4, .g5 {
  position: absolute;
  width: var(--circle-size);
  height: var(--circle-size);
  top: calc(50% - var(--circle-size) / 2);
  left: calc(50% - var(--circle-size) / 2);
  mix-blend-mode: var(--blending);
  transition: background-size 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.g1 {
  background: radial-gradient(circle at center, rgba(var(--color1),0.8) 0, rgba(var(--color1),0) v-bind(gSize));
}

.g2 {
  background: radial-gradient(circle at center, rgba(var(--color2),0.8) 0, rgba(var(--color2),0) v-bind(gSize));
  transform-origin: calc(50% - 400px);
}

.g3 {
  background: radial-gradient(circle at center, rgba(var(--color3),0.8) 0, rgba(var(--color3),0) v-bind(gSize));
  top: calc(50% - var(--circle-size)/2 + 200px);
  left: calc(50% - var(--circle-size)/2 - 500px);
  transform-origin: calc(50% + 400px);
}

.g4 {
  background: radial-gradient(circle at center, rgba(var(--color4),0.8) 0, rgba(var(--color4),0) v-bind(gSize));
  transform-origin: calc(50% - 200px);
  opacity: 0.7;
}

.g5 {
  background: radial-gradient(circle at center, rgba(var(--color5),0.8) 0, rgba(var(--color5),0) v-bind(gSize));
  width: calc(var(--circle-size) * 2);
  height: calc(var(--circle-size) * 2);
  top: calc(50% - var(--circle-size));
  left: calc(50% - var(--circle-size));
  transform-origin: calc(50% - 800px) calc(50% + 200px);
}

.interactive {
  position: absolute;
  width: 100%;
  height: 100%;
  top: -50%;
  left: -50%;
  background: radial-gradient(circle at center, rgba(var(--color-interactive),0.8) 0, rgba(var(--color-interactive),0) 80%);
  mix-blend-mode: var(--blending);
  opacity: 0.1;
}

@keyframes moveInCircle {
  0% { transform: rotate(0deg); }
  50% { transform: rotate(180deg); }
  100% { transform: rotate(360deg); }
}

@keyframes moveVertical {
  0% { transform: translateY(-50%); }
  50% { transform: translateY(50%); }
  100% { transform: translateY(-50%); }
}

@keyframes moveHorizontal {
  0% { transform: translateX(-50%) translateY(-10%); }
  50% { transform: translateX(50%) translateY(10%); }
  100% { transform: translateX(-50%) translateY(-10%); }
}
</style>
