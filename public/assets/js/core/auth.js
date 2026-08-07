// KayaCMS Auth Module
const Auth = {
    async login(email, password) {
        try {
            const data = await api.post('/auth/login', { email, password });
            api.setToken(data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            return data;
        } catch (error) {
            throw error;
        }
    },

    logout() {
        api.setToken(null);
        localStorage.removeItem('user');
        window.location.href = '/admin/login';
    },

    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },

    isAuthenticated() {
        return !!localStorage.getItem('token');
    },

    async me() {
        try {
            const data = await api.get('/auth/me');
            localStorage.setItem('user', JSON.stringify(data.user));
            return data.user;
        } catch (error) {
            this.logout();
            throw error;
        }
    }
};

window.Auth = Auth;
