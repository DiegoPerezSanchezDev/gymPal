import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';

export function useTimeAgo() {
    const timeAgo = (date) => {
        if (!date) return '';
        try {
            return formatDistanceToNow(new Date(date), {
                addSuffix: true,
                locale: es
            });
        } catch (e) {
            return '';
        }
    };

    return {
        timeAgo
    };
}
