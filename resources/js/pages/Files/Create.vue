<template>
  <div>
    <h1>Create File</h1>
    <form @submit.prevent="submit">
      <div>
        <label for="name">Name</label>
        <input type="text" v-model="form.name" id="name" />
      </div>
      <div>
        <label for="file">File</label>
        <input type="file" @change="handleFileUpload" id="file" />
      </div>
      <button type="submit">Submit</button>
    </form>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
  setup() {
    const form = useForm({
      name: '',
      file: null,
    });

    const handleFileUpload = (event) => {
      form.file = event.target.files[0];
    };

    const submit = () => {
      form.post('/files');
    };

    return { form, handleFileUpload, submit };
  },
};
</script>
