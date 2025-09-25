<template>
  <div>
    <!-- Language Switcher -->
    <div class="flex justify-end p-4">
  <button :class="['px-4 py-2 rounded', lang === 'en' ? 'bg-yellow-500 text-gray-900' : 'bg-gray-200 text-gray-700']" @click="lang = 'en'">English</button>
  <button :class="['px-4 py-2 rounded ml-2', lang === 'ar' ? 'bg-yellow-500 text-gray-900' : 'bg-gray-200 text-gray-700']" @click="lang = 'ar'">العربية</button>
    </div>
    <div class="landing-page">
    <!-- Hero Section with Parallax Effect -->
    <section id="hero" class="hero-section relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/40 z-10"></div>
      
      <div class="parallax-bg" ref="parallaxBg"></div>
      
      <div class="container mx-auto px-4 py-32 lg:py-40 relative z-20">
        <div class="flex flex-col items-center justify-center mb-8">
          <img src="/src/assets/logo.svg" alt="Logo" class="logo-landing mb-6" />
        </div>
        <div class="max-w-3xl" data-aos="fade-right" data-aos-duration="1200">
          <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
            {{ t.heroTitle || 'Loading...' }}
          </h1>
          <p class="text-xl text-white/90 mb-8">
            {{ t.heroDesc || 'Loading...' }}
          </p>
          <div class="flex flex-wrap gap-4">
            <router-link to="/login" class="btn-primary px-8 py-3 font-medium rounded-md bg-gradient-to-r from-yellow-500 to-yellow-400 text-gray-900 hover:from-yellow-400 hover:to-yellow-500 transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-yellow-500/25">
              {{ t.reserveNow || 'Loading...' }}
            </router-link>
            <a href="#vehicles" class="btn-secondary px-8 py-3 font-medium rounded-md bg-white/10 text-white border border-white/20 backdrop-blur-sm hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-1">
              {{ t.viewFleet || 'Loading...' }}
            </a>
          </div>
        </div>
      </div>
      
      <!-- Cars showcase with animation -->
      <div class="car-slider absolute bottom-0 right-0 z-10" ref="carSlider">
        <div v-for="(car, index) in cars" :key="index" :class="['car-slide', {'active': activeCar === index}]">
          <img :src="car.image" :alt="car.name" class="w-full h-auto" />
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ t.whyTitle || 'Loading...' }}</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ t.whyDesc || 'Loading...' }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="100">
            <div class="icon-wrapper w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ t.premiumSelection }}</h3>
            <p class="text-gray-600">{{ t.premiumSelectionDesc }}</p>
          </div>
          
          <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="200">
            <div class="icon-wrapper w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ t.support }}</h3>
            <p class="text-gray-600">{{ t.supportDesc }}</p>
          </div>
          
          <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="300">
            <div class="icon-wrapper w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ t.bestRates }}</h3>
            <p class="text-gray-600">{{ t.bestRatesDesc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Vehicle Fleet Section with animation -->
    <section id="vehicles" class="py-20">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ t.fleetTitle }}</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ t.fleetDesc }}</p>
        </div>

        <div class="flex flex-wrap justify-center gap-8">
          <div v-for="(vehicle, index) in vehicles" :key="index" 
               class="vehicle-card w-full md:w-96 bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
               data-aos="fade-up" 
               :data-aos-delay="100 + (index * 100)">
            <div class="relative overflow-hidden h-64">
              <img :src="vehicle.image" :alt="vehicle.name" class="w-full h-full object-cover transition-transform duration-700 hover:scale-110">
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end p-6">
                <div>
                  <h3 class="text-2xl font-bold text-white">{{ vehicle.name }}</h3>
                  <div class="flex items-center mt-2">
                    <span class="text-yellow-400 text-lg font-semibold">{{ t.omr }} {{ vehicle.price }}/day</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="p-6">
              <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  {{ vehicle.doors }} {{ t.doors }}
                </div>
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ vehicle.seats }} {{ t.seats }}
                </div>
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                  </svg>
                  {{ t.transmission }}: {{ vehicle.transmission }}
                </div>
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                  </svg>
                  {{ t.type }}: {{ vehicle.type }}
                </div>
              </div>
              <p class="text-gray-600 mb-6">{{ vehicle.description }}</p>
              <div class="flex justify-between">
                <button class="btn-secondary px-4 py-2 text-sm font-medium rounded bg-gray-100 text-gray-800 hover:bg-gray-200 transition-all">{{ t.viewDetails }}</button>
                <router-link to="/login" class="btn-primary px-4 py-2 text-sm font-medium rounded bg-yellow-500 text-gray-900 hover:bg-yellow-400 transition-all">{{ t.bookNow }}</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center">
          <div class="w-full lg:w-1/2 mb-12 lg:mb-0" data-aos="fade-right">
            <div class="relative">
              <div class="about-image-grid grid grid-cols-2 gap-4">
                <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8Y2FyJTIwcmVudGFsfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="About Al Ibdal" class="rounded-lg shadow-lg mb-4 w-full h-64 object-cover">
                <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8Y2FyJTIwcmVudGFsfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="About Al Ibdal" class="rounded-lg shadow-lg w-full h-64 object-cover translate-y-8">
              </div>
              <div class="absolute -bottom-8 -right-8 w-36 h-36 bg-yellow-400 rounded-full z-0 hidden lg:block"></div>
            </div>
          </div>
          <div class="w-full lg:w-1/2 lg:pl-16" data-aos="fade-left">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">{{ t.aboutTitle }}</h2>
            <p class="text-lg text-gray-600 mb-6">
              {{ t.aboutDesc }}
            </p>
            <p class="text-lg text-gray-600 mb-6">
              {{ t.aboutMission }}
            </p>
            <div class="grid grid-cols-2 gap-6 mb-8">
              <div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">10+ Years</h4>
                <p class="text-gray-600">Industry Experience</p>
              </div>
              <div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">500+</h4>
                <p class="text-gray-600">Premium Vehicles</p>
              </div>
              <div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">4</h4>
                <p class="text-gray-600">Strategic Branches</p>
              </div>
              <div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">24/7</h4>
                <p class="text-gray-600">Customer Support</p>
              </div>
            </div>
            <a href="#contact" class="btn-primary px-8 py-3 font-medium rounded-md bg-gradient-to-r from-yellow-500 to-yellow-400 text-gray-900 hover:from-yellow-400 hover:to-yellow-500 transition-all duration-300">{{ t.contactUs }}</a>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Testimonials -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ t.testimonialsTitle }}</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ t.testimonialsDesc }}</p>
        </div>
        
        <div class="flex flex-wrap justify-center gap-8">
          <div v-for="(testimonial, index) in testimonials" :key="index" 
               class="testimonial-card w-full md:w-96 bg-white p-8 rounded-xl shadow-lg"
               data-aos="fade-up"
               :data-aos-delay="index * 100">
            <div class="flex items-center mb-6">
              <div class="w-12 h-12 rounded-full overflow-hidden mr-4">
                <img :src="testimonial.avatar" :alt="testimonial.name" class="w-full h-full object-cover">
              </div>
              <div>
                <h4 class="font-bold text-gray-900">{{ testimonial.name }}</h4>
                <p class="text-sm text-gray-600">{{ testimonial.location }}</p>
              </div>
              <div class="ml-auto">
                <div class="flex text-yellow-400">
                  <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="i <= testimonial.rating ? 'text-yellow-400' : 'text-gray-300'" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
              </div>
            </div>
            <p class="text-gray-600">{{ testimonial.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ t.contactTitle }}</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ t.contactDesc }}</p>
        </div>
        
        <div class="flex flex-wrap -mx-4">
          <div class="w-full lg:w-1/2 px-4 mb-10 lg:mb-0" data-aos="fade-right">
            <div class="bg-white p-8 rounded-xl shadow-lg h-full">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h3>
              
              <div class="space-y-6">
                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-medium text-gray-900">Phone</h4>
                    <p class="text-gray-600">77307045</p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-medium text-gray-900">Email</h4>
                    <p class="text-gray-600">support@ALIBDALTRADING.COM</p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-medium text-gray-900">Address</h4>
                    <p class="text-gray-600">AL IBDAL TRADING LLC</p>
                    <p class="text-gray-600">Muscat, Sultanate of Oman</p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-medium text-gray-900">Working Hours</h4>
                    <p class="text-gray-600">Monday - Friday: 9:00 AM - 6:00 PM</p>
                    <p class="text-gray-600">Saturday: 10:00 AM - 4:00 PM</p>
                  </div>
                </div>
              </div>
              
              <div class="mt-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Follow Us</h4>
                <div class="flex space-x-4">
                  <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-all">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                    </svg>
                  </a>
                  <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-all">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path>
                    </svg>
                  </a>
                  <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-all">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"></path>
                    </svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
          
          <div class="w-full lg:w-1/2 px-4" data-aos="fade-left">
            <div class="bg-white p-8 rounded-xl shadow-lg">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">Send Us a Message</h3>
              
              <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                  </div>
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                  </div>
                </div>
                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                  <input type="text" id="subject" name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                  <textarea id="message" name="message" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                </div>
                <button type="submit" class="w-full px-8 py-3 font-medium rounded-md bg-gradient-to-r from-yellow-500 to-yellow-400 text-gray-900 hover:from-yellow-400 hover:to-yellow-500 transition-all duration-300">Send Message</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Map Section -->
    <section id="branches-map" class="py-20">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ t.branchesTitle }}</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ t.branchesDesc }}</p>
        </div>
        
        <div class="h-96 rounded-xl overflow-hidden shadow-lg" ref="branchesMap">
          <!-- Map will be rendered here by Leaflet -->
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
          <div v-for="(branch, index) in branches" :key="index" 
               class="branch-card bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all"
               data-aos="fade-up"
               :data-aos-delay="index * 100"
               @click="highlightBranch(index)">
            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ branch.name }}</h3>
            <p class="text-gray-600 mb-2">{{ branch.address }}</p>
            <p class="text-gray-600 text-sm">Phone: {{ branch.phone }}</p>
            <p class="text-gray-600 text-sm">Email: {{ branch.email }}</p>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Footer section will be added separately -->
    </div>
  </div>
</template>

<script setup>
const translations = {
  en: {
    heroTitle: 'Premium Car Rental Experience',
    heroDesc: 'Discover the joy of driving premium vehicles with Al Ibdal Trading LLC. We offer a wide range of luxury and economy vehicles to suit your needs.',
    reserveNow: 'Reserve Now',
    viewFleet: 'View Fleet',
    whyTitle: 'Why Choose Al Ibdal',
    whyDesc: 'Experience the best car rental service in Oman with our premium fleet and exceptional customer service.',
    premiumSelection: 'Premium Selection',
    premiumSelectionDesc: 'Choose from our wide range of premium and luxury vehicles to suit any occasion or requirement.',
    support: '24/7 Support',
    supportDesc: 'Our customer support team is available round the clock to assist you with any queries or concerns.',
    bestRates: 'Best Rates',
    bestRatesDesc: 'We offer the most competitive pricing in the market with transparent billing and no hidden charges.',
    fleetTitle: 'Our Premium Fleet',
    fleetDesc: 'Explore our collection of meticulously maintained vehicles for your perfect journey.',
    aboutTitle: 'About Al Ibdal Trading LLC',
    aboutDesc: 'AL IBDAL TRADING LLC is a premier car rental company in Oman, offering a wide range of vehicles from economy to luxury cars. With over a decade of experience in the industry, we pride ourselves on providing exceptional service to our customers across the Sultanate of Oman.',
    aboutMission: 'Our mission is to make car rentals hassle-free and enjoyable, with transparent pricing, well-maintained vehicles, and customer-centric service.',
    years: '10+ Years',
    experience: 'Industry Experience',
    vehicles: '500+',
    premiumVehicles: 'Premium Vehicles',
    branches: '4',
    strategicBranches: 'Strategic Branches',
    support247: '24/7',
    customerSupport: 'Customer Support',
    contactUs: 'Contact Us',
    testimonialsTitle: 'What Our Clients Say',
    testimonialsDesc: 'Hear from our satisfied customers about their experiences with Al Ibdal Trading LLC.',
    contactTitle: 'Get In Touch',
    contactDesc: "Have questions or need assistance? Contact us and we'll be happy to help.",
    branchesTitle: 'Our Branches',
    branchesDesc: 'Find us at convenient locations across Oman.',
    phone: 'Phone',
    email: 'Email',
    address: 'Address',
    workingHours: 'Working Hours',
    saturdayThu: 'Saturday - Thu: 9:00 AM - 6:00 PM',
    friday: 'Friday: Closed',
    omr: 'OMR',
    bookNow: 'Book Now',
    viewDetails: 'View Details',
    doors: 'Doors',
    seats: 'Seats',
    transmission: 'Transmission',
    type: 'Type',
  },
  ar: {
    heroTitle: 'تجربة تأجير سيارات فاخرة',
    heroDesc: 'اكتشف متعة قيادة السيارات الفاخرة مع شركة البدال. نقدم مجموعة واسعة من السيارات الفاخرة والاقتصادية لتناسب احتياجاتك.',
    reserveNow: 'احجز الآن',
    viewFleet: 'عرض الأسطول',
    whyTitle: 'لماذا تختار البدال',
    whyDesc: 'استمتع بأفضل خدمة تأجير سيارات في عمان مع أسطولنا المميز وخدمة العملاء الاستثنائية.',
    premiumSelection: 'اختيار فاخر',
    premiumSelectionDesc: 'اختر من مجموعتنا الواسعة من السيارات الفاخرة لتناسب جميع المناسبات والاحتياجات.',
    support: 'دعم على مدار الساعة',
    supportDesc: 'فريق خدمة العملاء متواجد دائماً لمساعدتك في أي استفسار أو مشكلة.',
    bestRates: 'أفضل الأسعار',
    bestRatesDesc: 'نقدم أفضل الأسعار في السوق مع شفافية في الفواتير وبدون رسوم خفية.',
    fleetTitle: 'أسطولنا المميز',
    fleetDesc: 'استكشف مجموعتنا من السيارات التي يتم صيانتها بعناية لرحلتك المثالية.',
    aboutTitle: 'عن شركة البدال',
    aboutDesc: 'شركة البدال هي شركة رائدة في تأجير السيارات في سلطنة عمان، تقدم مجموعة واسعة من السيارات من الاقتصادية إلى الفاخرة. مع أكثر من عشر سنوات من الخبرة، نفخر بتقديم خدمة استثنائية لعملائنا في جميع أنحاء السلطنة.',
    aboutMission: 'مهمتنا هي جعل تأجير السيارات سهلاً وممتعاً، مع أسعار شفافة وسيارات بحالة ممتازة وخدمة تركز على العميل.',
    years: 'أكثر من 10 سنوات',
    experience: 'خبرة في المجال',
    vehicles: '500+',
    premiumVehicles: 'سيارات فاخرة',
    branches: '4',
    strategicBranches: 'فروع استراتيجية',
    support247: '24/7',
    customerSupport: 'دعم العملاء',
    contactUs: 'اتصل بنا',
    testimonialsTitle: 'آراء عملائنا',
    testimonialsDesc: 'استمع إلى آراء عملائنا الراضين عن تجربتهم مع شركة البدال.',
    contactTitle: 'تواصل معنا',
    contactDesc: 'هل لديك أسئلة أو تحتاج إلى مساعدة؟ تواصل معنا وسنكون سعداء بخدمتك.',
    branchesTitle: 'فروعنا',
    branchesDesc: 'تجدنا في مواقع مميزة في جميع أنحاء عمان.',
    phone: 'الهاتف',
    email: 'البريد الإلكتروني',
    address: 'العنوان',
    workingHours: 'ساعات العمل',
    saturdayThu: 'السبت - الخميس: 9:00 صباحاً - 6:00 مساءً',
    friday: 'الجمعة: مغلق',
    omr: 'ريال عماني',
    bookNow: 'احجز الآن',
    viewDetails: 'عرض التفاصيل',
    doors: 'أبواب',
    seats: 'مقاعد',
    transmission: 'ناقل الحركة',
    type: 'النوع',
  }
};
import { ref, computed, onMounted, onUnmounted } from 'vue';
// Language state
const lang = ref('en');

// Translation object
const t = computed(() => {
  if (lang.value && translations[lang.value]) return translations[lang.value];
  return translations.en;
});

// Heavy libs are dynamically imported in onMounted to keep initial bundle small
// Keep CSS side-effects loaded globally (lightweight)
import 'aos/dist/aos.css';
import 'leaflet/dist/leaflet.css';

let AOSLib = null
let GSAP = null
let ScrollTriggerLib = null
let Leaflet = null

// References for DOM elements
const parallaxBg = ref(null);
const carSlider = ref(null);
const branchesMap = ref(null);
let map = null;
let markers = [];

// Car showcase data
const activeCar = ref(0);
const cars = [
  { name: 'Mercedes S-Class', image: 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1025&q=80' },
  { name: 'BMW X5', image: 'https://images.unsplash.com/photo-1556189250-72ba954cfc2b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80' },
  { name: 'Audi A6', image: 'https://images.unsplash.com/photo-1610647752706-3bb12232b3c4?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1025&q=80' }
];

// Vehicle fleet data
const vehicles = [
  {
    name: 'Mercedes-Benz S-Class',
    image: 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1025&q=80',
    price: '750',
    doors: 4,
    seats: 5,
    transmission: 'Automatic',
    type: 'Luxury',
    description: 'Experience luxury and comfort with our Mercedes-Benz S-Class, featuring premium leather seats, advanced navigation, and superior sound system.'
  },
  {
    name: 'BMW X5',
    image: 'https://images.unsplash.com/photo-1556189250-72ba954cfc2b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
    price: '550',
    doors: 5,
    seats: 7,
    transmission: 'Automatic',
    type: 'SUV',
    description: 'The perfect SUV for family trips or off-road adventures, offering spacious interiors, advanced safety features, and powerful performance.'
  },
  {
    name: 'Audi A6',
    image: 'https://images.unsplash.com/photo-1610647752706-3bb12232b3c4?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1025&q=80',
    price: '450',
    doors: 4,
    seats: 5,
    transmission: 'Automatic',
    type: 'Sedan',
    description: 'A perfect blend of luxury and performance, the Audi A6 offers refined interiors, cutting-edge technology, and smooth driving experience.'
  },
  {
    name: 'Toyota Land Cruiser',
    image: 'https://images.unsplash.com/photo-1622542796254-5b9c46ab0f2f?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1025&q=80',
    price: '600',
    doors: 5,
    seats: 8,
    transmission: 'Automatic',
    type: 'SUV',
    description: 'Conquer any terrain with the legendary Toyota Land Cruiser, known for its reliability, durability, and off-road capabilities.'
  }
];

// Testimonial data (Arabic reviewers from Oman)
const testimonials = [
  {
    name: 'سعيد البوسعيدي',
    avatar: 'https://randomuser.me/api/portraits/men/12.jpg',
    location: 'مسقط، سلطنة عمان',
    rating: 5,
    text: 'خدمة ممتازة وسريعة. السيارات نظيفة وفريق العمل متعاون جداً. أنصح الجميع بالتعامل مع البدال.'
  },
  {
    name: 'مريم الشكيلية',
    avatar: 'https://randomuser.me/api/portraits/women/15.jpg',
    location: 'صلالة، سلطنة عمان',
    rating: 5,
    text: 'تجربة رائعة مع شركة البدال. الأسعار مناسبة والسيارة كانت بحالة ممتازة. شكراً لكم!'
  },
  {
    name: 'خالد الرواحي',
    avatar: 'https://randomuser.me/api/portraits/men/22.jpg',
    location: 'صحار، سلطنة عمان',
    rating: 4,
    text: 'أفضل شركة لتأجير السيارات في عمان. سرعة في الاستجابة وراحة في التعامل.'
  }
];

// Branch locations data (Oman)
const branches = [
  {
    name: 'Muscat - Al Khuwair Branch',
    city: 'Muscat',
    address: 'Al Khuwair, Muscat, Sultanate of Oman',
    email: 'muscat.khuwair@alibdaltrading.com',
    phone: '77307045',
    location: [23.61432800, 58.5452840]
  },
  {
    name: 'Muscat - Qurum Branch',
    city: 'Muscat',
    address: 'Qurum, Muscat, Sultanate of Oman',
    email: 'muscat.qurum@alibdaltrading.com',
    phone: '77307046',
    location: [23.60884700, 58.4902840]
  },
  {
    name: 'Sohar Branch',
    city: 'Sohar',
    address: 'Central Sohar, Sultanate of Oman',
    email: 'sohar@alibdaltrading.com',
    phone: '77307047',
    location: [24.34115000, 56.7096610]
  },
  {
    name: 'Salalah Branch',
    city: 'Salalah',
    address: 'Central Salalah, Dhofar, Sultanate of Oman',
    email: 'salalah@alibdaltrading.com',
    phone: '77307048',
    location: [17.01547600, 54.09234100]
  }
];

// Car slider animation
const startCarSlider = () => {
  setInterval(() => {
    activeCar.value = (activeCar.value + 1) % cars.length;
  }, 5000);
};

// Initialize map with branch locations
const initMap = () => {
  if (!branchesMap.value) return;

  // Create map centered on UAE
  if (!Leaflet) return
  map = Leaflet.map(branchesMap.value).setView([25.276987, 55.296249], 8);
  
  // Add OpenStreetMap tiles
  Leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  
  // Add markers for each branch
  branches.forEach((branch, index) => {
    const marker = Leaflet.marker(branch.location)
      .addTo(map)
      .bindPopup(`
        <strong>${branch.name}</strong><br>
        ${branch.address}<br>
        ${branch.phone}
      `);
    
    markers.push(marker);
  });
};

// Highlight a branch on the map
const highlightBranch = (index) => {
  if (map && markers[index]) {
    map.setView(branches[index].location, 12);
    markers[index].openPopup();
  }
};

// Setup parallax effect
const setupParallax = () => {
  if (!parallaxBg.value) return;
  
  // Set initial background
  parallaxBg.value.style.backgroundImage = `url('https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1332&q=80')`;
  parallaxBg.value.style.backgroundSize = 'cover';
  parallaxBg.value.style.backgroundPosition = 'center';
  parallaxBg.value.style.position = 'absolute';
  parallaxBg.value.style.top = '0';
  parallaxBg.value.style.left = '0';
  parallaxBg.value.style.width = '100%';
  parallaxBg.value.style.height = '100%';
  
  // Setup GSAP ScrollTrigger for parallax effect
  if (!GSAP || !ScrollTriggerLib) return
  GSAP.registerPlugin(ScrollTriggerLib)
  GSAP.to(parallaxBg.value, {
    backgroundPosition: '50% 30%',
    ease: 'none',
    scrollTrigger: {
      trigger: parallaxBg.value,
      start: 'top top',
      end: 'bottom top',
      scrub: true
    }
  });
};

onMounted(async () => {
  // Dynamic imports for heavy libs
  const [AOSModule, GSAPModule, ScrollTriggerModule, LeafletModule] = await Promise.all([
    import('aos'),
    import('gsap'),
    import('gsap/ScrollTrigger'),
    import('leaflet')
  ])

  AOSLib = AOSModule.default
  GSAP = GSAPModule.default
  ScrollTriggerLib = ScrollTriggerModule.ScrollTrigger || ScrollTriggerModule.default || ScrollTriggerModule
  Leaflet = LeafletModule.default

  // Initialize AOS animation library
  AOSLib?.init({
    duration: 1000,
    once: true,
    offset: 100
  })
  
  // Setup parallax effect
  setupParallax()
  
  // Start car slider
  startCarSlider()
  
  // Initialize the map
  initMap()
});

onUnmounted(() => {
  // Clean up the map instance
  if (map) {
    map.remove();
    map = null;
  }
});
</script>

<style scoped>
.hero-section {
  height: 100vh;
  min-height: 600px;
  position: relative;
  overflow: hidden;
}

.logo-landing {
  height: 80px;
  width: auto;
  display: block;
  animation: logoFadeInScale 1.2s cubic-bezier(.23,1.01,.32,1) 0.2s both;
}

@keyframes logoFadeInScale {
  0% { opacity: 0; transform: scale(0.7) translateY(-30px); }
  60% { opacity: 1; transform: scale(1.08) translateY(0); }
  100% { opacity: 1; transform: scale(1) translateY(0); }
}

.parallax-bg {
  background-image: url('https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1332&q=80');
  background-size: cover;
  background-position: center;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

.car-slider {
  max-width: 50%;
  transform: translateY(20%);
}

.car-slide {
  display: none;
  animation: fadeIn 1s ease forwards;
}

.car-slide.active {
  display: block;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.feature-card, .vehicle-card, .testimonial-card, .branch-card {
  transition: all 0.3s ease;
}

.feature-card:hover, .branch-card:hover {
  transform: translateY(-5px);
}

.vehicle-card:hover {
  transform: translateY(-8px);
}

.about-image-grid img {
  transition: transform 0.5s ease;
}

.about-image-grid img:hover {
  transform: scale(1.03);
}

.activity-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.375rem;
  height: 0.75rem;
}
</style>
