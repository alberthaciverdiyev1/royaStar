import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Login from '../pages/Login.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import Dashboard from '../pages/Dashboard.vue'
import CityList from '../pages/cities/CityList.vue'
import GradeList from '../pages/grades/GradeList.vue'
import TopicList from '../pages/topics/TopicList.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true },
  },
  {
    path: '/',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'Dashboard', component: Dashboard },
      { path: 'cities', name: 'Cities', component: CityList },
      { path: 'grades', name: 'Grades', component: GradeList },
      { path: 'topics', name: 'Topics', component: TopicList },
      { path: 'lessons', name: 'Lessons', component: () => import('../pages/lessons/LessonList.vue') },
      { path: 'lesson-reviews', name: 'LessonReviews', component: () => import('../pages/lessons/LessonReviews.vue') },
      { path: 'questions', name: 'Questions', component: () => import('../pages/questions/QuestionList.vue') },
      { path: 'quizzes', name: 'Quizzes', component: () => import('../pages/quizzes/QuizList.vue') },
      { path: 'quizzes/:id', name: 'QuizDetail', component: () => import('../pages/quizzes/QuizDetail.vue') },
      { path: 'exams', name: 'Exams', component: () => import('../pages/exams/ExamList.vue') },
      { path: 'exams/:id', name: 'ExamDetail', component: () => import('../pages/exams/ExamDetail.vue') },
      { path: 'stars', name: 'Stars', component: () => import('../pages/stars/StarList.vue') },
      { path: 'students', name: 'Students', component: () => import('../pages/students/StudentList.vue') },
      { path: 'users', name: 'Users', component: () => import('../pages/users/UserList.vue') },
      { path: 'users/pending', name: 'PendingUsers', component: () => import('../pages/users/UserList.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth) {
    if (!auth.isAuthenticated) {
      return { name: 'Login' }
    }
    if (auth.user && auth.user.type !== 'admin') {
      auth.logout()
      return { name: 'Login' }
    }
  }

  if (to.meta.guest && auth.isAuthenticated && auth.user?.type === 'admin') {
    return { name: 'Dashboard' }
  }
})

export default router
