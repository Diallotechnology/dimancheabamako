<template>
    <Layout>
        <!-- Start Hero Section -->
        <div class="hero">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-5">
                        <div class="intro-excerpt">
                            <h1>
                                Modern Interior
                                <span clsas="d-block">Design Studio</span>
                            </h1>
                            <p class="mb-4">
                                Donec vitae odio quis nisl dapibus malesuada.
                                Nullam ac aliquet velit. Aliquam vulputate velit
                                imperdiet dolor tempor tristique.
                            </p>
                            <p>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="lname"
                                />
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="hero-img-wrap">
                            <!-- <img
                                :src="asset / images / couch.png"
                                class="img-fluid"
                            /> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Hero Section -->
    </Layout>
</template>
<script setup>
import Layout from "../Shared/Layout.vue";
</script>
