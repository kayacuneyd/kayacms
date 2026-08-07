// Simple hash-based SPA router
class Router {
    constructor() {
        this.routes = {};
        this.currentRoute = null;
        window.addEventListener('hashchange', () => this.handleRoute());
        window.addEventListener('load', () => this.handleRoute());
    }

    register(path, handler) {
        this.routes[path] = handler;
    }

    navigate(path) {
        window.location.hash = path;
    }

    handleRoute() {
        const hash = window.location.hash.slice(1) || '/dashboard';
        const [path, ...params] = hash.split('/').filter(Boolean);
        const route = `/${path}`;

        if (this.routes[route]) {
            this.currentRoute = route;
            this.routes[route](params);
            this.updateActiveLinks(route);
        } else if (this.routes['/404']) {
            this.routes['/404']();
        }
    }

    updateActiveLinks(currentRoute) {
        document.querySelectorAll('.ck-sidebar-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-route') === currentRoute) {
                link.classList.add('active');
            }
        });
    }
}

window.router = new Router();
