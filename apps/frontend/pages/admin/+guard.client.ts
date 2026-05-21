import { redirect } from 'vike/abort';
import { authService } from '@/services/authService';

export { guard };

async function guard(pageContext: { urlPathname: string }) {
  const token = authService.getToken();
  const pathname = pageContext.urlPathname;

  if (pathname === '/admin/login') {
    if (token) {
      throw redirect('/admin/tours');
    }

    return;
  }

  if (!token) {
    throw redirect('/admin/login');
  }
}
