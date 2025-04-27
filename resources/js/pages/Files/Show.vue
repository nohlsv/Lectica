<template>
  <div>
    <h1>File Details</h1>
    <p><strong>Name:</strong> {{ file.name }}</p>
    <p><strong>Path:</strong> {{ file.path }}</p>
  </div>
</template>

<script>
export default {
  props: {
    file: Object,
  },
};
</script>
