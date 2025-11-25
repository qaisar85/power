import React, { useState, useMemo, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { 
    Globe, Users, Briefcase, TrendingUp, Shield, 
    ArrowRight, CheckCircle, Star, MapPin, ChevronLeft, ChevronRight,
    Building, FileText
} from 'lucide-react';

// Format numbers for display
function formatNumber(num) {
    if (!num) return '0';
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

export default function Welcome({ canLogin, canRegister, latestProducts = [], latestAuctions = [], seo = {}, locale = 'en', locales = ['en','ru','ar','fr'], categories = [], countries = [], stats = {} }) {
    const features = [
        {
            icon: Globe,
            title: 'Global Marketplace',
            description: `Connect with ${formatNumber(stats.companies || 2100000)}+ businesses across ${stats.countries || 195} countries`
        },
        {
            icon: Users,
            title: 'Jobs Portal',
            description: `${formatNumber(stats.jobs || 450000)}+ job opportunities for professionals and freelancers`
        },
        {
            icon: Briefcase,
            title: 'Tenders & Auctions',
            description: 'Access government and private sector opportunities'
        },
        {
            icon: TrendingUp,
            title: 'Business Intelligence',
            description: 'Market insights and analytics for informed decisions'
        },
        {
            icon: Shield,
            title: 'Secure Platform',
            description: 'Enterprise-grade security with verified businesses'
        }
    ];

    const statsList = [
        { 
            value: stats.companies || 2100000, 
            label: 'Active Companies',
            icon: Building,
            color: 'text-blue-600',
            bgColor: 'bg-blue-50'
        },
        { 
            value: stats.countries || 195, 
            label: 'Countries',
            icon: MapPin,
            color: 'text-green-600',
            bgColor: 'bg-green-50'
        },
        { 
            value: stats.jobs || 450000, 
            label: 'Job Opportunities',
            icon: Users,
            color: 'text-purple-600',
            bgColor: 'bg-purple-50'
        },
        { 
            value: stats.tenders || 12000, 
            label: 'Active Tenders',
            icon: FileText,
            color: 'text-orange-600',
            bgColor: 'bg-orange-50'
        }
    ];

    const [search, setSearch] = useState('');
    const [country, setCountry] = useState('');
    const [category, setCategory] = useState('');

    // Simple hero slider with two slides
    const [activeSlide, setActiveSlide] = useState(0);
    const [isHovering, setIsHovering] = useState(false);
    const [failedVideoIdx, setFailedVideoIdx] = useState({});
    // Use local assets for reliability; keep same titles/subtitles
    const slides = [
        {
            type: 'image',
            src: '/assets/hero-1.svg',
            title: "World's #1 Business Platform",
            subtitle: 'Connect with global businesses, find opportunities, hire talent, and grow worldwide.'
        },
        {
            type: 'image',
            src: '/assets/hero-2.svg',
            title: 'Reach New Markets Worldwide',
            subtitle: 'Discover products and services across 195 countries and 2.1M+ businesses.'
        }
    ];
    // Autoplay slides, pause on hover
    useEffect(() => {
        if (isHovering) return;
        const id = setInterval(() => {
            setActiveSlide((i) => (i + 1) % slides.length);
        }, 6000);
        return () => clearInterval(id);
    }, [isHovering]);

    // Category translation labels per locale
    const catTranslations = {
        ru: {
            'Technology': 'Технологии',
            'Manufacturing': 'Производство',
            'Healthcare': 'Здравоохранение',
            'Finance': 'Финансы',
            'Retail & E-commerce': 'Ритейл и e-commerce',
            'Logistics & Mobility': 'Логистика и мобильность',
            'Energy & Utilities': 'Энергетика и коммунальные услуги',
            'Software Development': 'Разработка ПО',
            'AI & Data Science': 'ИИ и анализ данных',
            'Cybersecurity': 'Кибербезопасность',
            'Cloud & DevOps': 'Облако и DevOps',
            'IoT & Embedded': 'IoT и встроенные системы',
            'IT Services': 'ИТ-услуги',
            // Added global sectors
            'Agriculture & Forestry': 'Сельское хозяйство и лесное хозяйство',
            'Mining & Metals': 'Добыча и металлы',
            'Construction & Real Estate': 'Строительство и недвижимость',
            'Transportation & Logistics': 'Транспорт и логистика',
            'Information Technology': 'Информационные технологии',
            'Telecommunications': 'Телекоммуникации',
            'Financial Services': 'Финансовые услуги',
            'Healthcare & Life Sciences': 'Здравоохранение и бионауки',
            'Media & Entertainment': 'Медиа и развлечения',
            'Hospitality & Tourism': 'Гостеприимство и туризм',
            'Education & Training': 'Образование и обучение',
            'Government & Public Sector': 'Государственный и публичный сектор',
            'Environmental & Waste Management': 'Экология и управление отходами',
            'Professional Services': 'Профессиональные услуги',
            'Automotive & Mobility': 'Автомобильная отрасль и мобильность',
            'Chemicals & Materials': 'Химия и материалы',
            'Aerospace & Defense': 'Авиакосмическая отрасль и оборона',
            'Consumer Goods': 'Товары народного потребления',
        },
        ar: {
            'Technology': 'التكنولوجيا',
            'Manufacturing': 'التصنيع',
            'Healthcare': 'الرعاية الصحية',
            'Finance': 'المالية',
            'Retail & E-commerce': 'التجزئة والتجارة الإلكترونية',
            'Logistics & Mobility': 'اللوجستيات والتنقل',
            'Energy & Utilities': 'الطاقة والمرافق',
            'Software Development': 'تطوير البرمجيات',
            'AI & Data Science': 'الذكاء الاصطناعي وعلوم البيانات',
            'Cybersecurity': 'الأمن السيبراني',
            'Cloud & DevOps': 'السحابة و DevOps',
            'IoT & Embedded': 'إنترنت الأشياء والأنظمجة',
            'IT Services': 'خدمات تقنية المعلومات',
            // Added global sectors
            'Agriculture & Forestry': 'الزراعة والغابات',
            'Mining & Metals': 'التعدين والمعادن',
            'Construction & Real Estate': 'البناء والعقارات',
            'Transportation & Logistics': 'النقل والخدمات اللوجستية',
            'Information Technology': 'تقنية المعلومات',
            'Telecommunications': 'الاتصالات',
            'Financial Services': 'الخدمات المالية',
            'Healthcare & Life Sciences': 'الرعاية الصحية والعلوم الحياتية',
            'Media & Entertainment': 'الإعلام والترفيه',
            'Hospitality & Tourism': 'الضيافة والسياحة',
            'Education & Training': 'التعليم والتدريب',
            'Government & Public Sector': 'الحكومة والقطاع العام',
            'Environmental & Waste Management': 'البيئة وإدارة النفايات',
            'Professional Services': 'الخدمات المهنية',
            'Automotive & Mobility': 'السيارات والتنقل',
            'Chemicals & Materials': 'الكيماويات والمواد',
            'Aerospace & Defense': 'الطيران والفضاء والدفاع',
            'Consumer Goods': 'السلع الاستهلاكية',
        },
        fr: {
            'Technology': 'Technologie',
            'Manufacturing': 'Fabrication',
            'Healthcare': 'Santé',
            'Finance': 'Finance',
            'Retail & E-commerce': 'Retail et e-commerce',
            'Logistics & Mobility': 'Logistique et mobilité',
            'Energy & Utilities': 'Énergie et services publics',
            'Software Development': 'Développement logiciel',
            'AI & Data Science': 'IA et science des données',
            'Cybersecurity': 'Cybersécurité',
            'Cloud & DevOps': 'Cloud et DevOps',
            'IoT & Embedded': 'IoT et systèmes embarqués',
            'IT Services': 'Services informatiques',
            // Added global sectors
            'Agriculture & Forestry': 'Agriculture et foresterie',
            'Mining & Metals': 'Mines et métaux',
            'Construction & Real Estate': 'Construction et immobilier',
            'Transportation & Logistics': 'Transport et logistique',
            'Information Technology': 'Technologies de l’information',
            'Telecommunications': 'Télécommunications',
            'Financial Services': 'Services financiers',
            'Healthcare & Life Sciences': 'Santé et sciences de la vie',
            'Media & Entertainment': 'Médias et divertissement',
            'Hospitality & Tourism': 'Hôtellerie et tourisme',
            'Education & Training': 'Éducation et formation',
            'Government & Public Sector': 'Administration et secteur public',
            'Environmental & Waste Management': 'Environnement et gestion des déchets',
            'Professional Services': 'Services professionnels',
            'Automotive & Mobility': 'Automobile et mobilité',
            'Chemicals & Materials': 'Chimie et matériaux',
            'Aerospace & Defense': 'Aérospatial et défense',
            'Consumer Goods': 'Biens de consommation',
        },
    };

    // Country names localization
    const countryTranslations = {
        ru: {
            'Russia': 'Россия', 'Russian Federation': 'Россия', 'USA': 'США', 'United States': 'Соединённые Штаты', 'United Kingdom': 'Великобритания', 'UK': 'Великобритания',
            'Germany': 'Германия', 'France': 'Франция', 'China': 'Китай', 'Japan': 'Япония', 'India': 'Индия', 'Canada': 'Канада', 'Italy': 'Италия', 'Spain': 'Испания',
        },
        ar: {
            'Russia': 'روسيا', 'Russian Federation': 'روسيا', 'USA': 'الولايات المتحدة', 'United States': 'الولايات المتحدة', 'United Kingdom': 'المملكة المتحدة', 'UK': 'المملكة المتحدة',
            'Germany': 'ألمانيا', 'France': 'فرنسا', 'China': 'الصين', 'Japan': 'اليابان', 'India': 'الهند', 'Canada': 'كندا', 'Italy': 'إيطاليا', 'Spain': 'إسبانيا',
        },
        fr: {
            'Russia': 'Russie', 'Russian Federation': 'Russie', 'USA': 'États-Unis', 'United States': 'États-Unis', 'United Kingdom': 'Royaume-Uni', 'UK': 'Royaume-Uni',
            'Germany': 'Allemagne', 'France': 'France', 'China': 'Chine', 'Japan': 'Japon', 'India': 'Inde', 'Canada': 'Canada', 'Italy': 'Italie', 'Spain': 'Espagne',
        },
    };

    const tCountry = (name) => {
        const dict = countryTranslations[locale] || {};
        return dict[name] || name;
    };

    // Prepare category options (flatten top-level only)
    const categoryOptions = useMemo(() => {
        const opts = [{ value: '', label: 'All Categories' }];
        (categories || []).forEach((cat) => {
            const label = (catTranslations[locale] || {})[cat] || cat;
            opts.push({ value: cat, label });
        });
        return opts;
    }, [categories, locale]);

    const goSearch = (e) => {
        e.preventDefault();
        const params = new URLSearchParams();
        if (search) params.set('q', search);
        if (country) params.set('country', country);
        if (category) params.set('category', category);
        window.location.href = `/marketplace?${params.toString()}`;
    };

    // Live auction countdown - simplified display (for demo data)
    const [now, setNow] = useState(Date.now());
    useEffect(() => {
        const id = setInterval(() => setNow(Date.now()), 1000);
        return () => clearInterval(id);
    }, []);

    const timeLeft = (endTs) => {
        if (!endTs) return '';
        const diff = endTs - now;
        if (diff <= 0) return 'Ended';
        const d = Math.floor(diff / (1000 * 60 * 60 * 24));
        const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const m = Math.floor((diff / (1000 * 60)) % 60);
        const s = Math.floor((diff / 1000) % 60);
        return `${d}d ${h}h ${m}m ${s}s`;
    };

    // Fallback for image poster when external CDN fails
    const [failedImageIdx, setFailedImageIdx] = useState({});

    return (
        <div className="bg-gray-50 min-h-screen">
            <Head>
                <title>{seo?.title ?? 'Power — Global Business Platform'}</title>
                <meta name="description" content={seo?.description ?? 'Discover products, tenders, jobs, and auctions across 195 countries.'} />
                {/* Canonical */}
                {seo?.canonical && <link rel="canonical" href={seo.canonical} />}
                {/* OG */}
                <meta property="og:title" content={seo?.ogTitle ?? 'Power — Global Business Platform'} />
                <meta property="og:description" content={seo?.ogDescription ?? 'Connect with businesses worldwide.'} />
                {seo?.ogImage && <meta property="og:image" content={seo.ogImage} />}
                {/* Locale tags */}
                <meta property="og:locale" content={locale} />
                {locales?.filter(l => l !== locale).map((l) => (
                    <meta key={`alt-${l}`} property="og:locale:alternate" content={l} />
                ))}
            </Head>

            {/* Navigation */}
            <header className="bg-white shadow-sm">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between py-4">
                        <div className="flex items-center space-x-3">
                            <Link href="/" className="flex items-center space-x-2">
                                <Shield className="h-6 w-6 text-indigo-600" />
                                <span className="font-bold text-xl text-gray-900">Power</span>
                            </Link>
                            <nav className="hidden md:flex items-center space-x-6">
                                <Link href="/marketplace" className="text-gray-600 hover:text-gray-900">Marketplace</Link>
                                <Link href="/tenders" className="text-gray-600 hover:text-gray-900">Tenders</Link>
                                <Link href="/auctions" className="text-gray-600 hover:text-gray-900">Auctions</Link>
                                <Link href="/jobs" className="text-gray-600 hover:text-gray-900">Jobs</Link>
                                <Link href="/contacts" className="text-gray-600 hover:text-gray-900">Contacts</Link>
                            </nav>
                        </div>
                        <div className="flex items-center space-x-6">
                            <div className="hidden md:flex items-center space-x-4">
                                <div>
                                    <select
                                        className="border border-gray-300 rounded-md px-3 py-2"
                                        value={locale}
                                        onChange={(e) => router.get(window.location.pathname, { locale: e.target.value }, { preserveState: false, replace: true })}
                                    >
                                        {locales.map((l) => (
                                            <option key={l} value={l}>{l.toUpperCase()}</option>
                                        ))}
                                    </select>
                                </div>
                                {canLogin && (
                                    <div className="flex items-center space-x-4">
                                        <Link
                                            href="/login"
                                            className="text-gray-600 hover:text-gray-900 font-medium"
                                        >
                                            Sign In
                                        </Link>
                                        {canRegister && (
                                            <Link
                                                href="/register"
                                                className="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 font-medium"
                                            >
                                                Get Started
                                            </Link>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {/* Hero Section with slider */}
                <section className="relative py-12">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="relative overflow-hidden rounded-2xl shadow-lg" onMouseEnter={() => setIsHovering(true)} onMouseLeave={() => setIsHovering(false)}>
                            <div className="aspect-video bg-black">
                                {slides[activeSlide].type === 'video' && !failedVideoIdx[activeSlide] ? (
                                    <video
                                        className="w-full h-full object-cover"
                                        autoPlay
                                        muted
                                        loop
                                        playsInline
                                        preload="auto"
                                        crossOrigin="anonymous"
                                        poster={slides[activeSlide].poster}
                                        onError={() => setFailedVideoIdx((prev) => ({ ...prev, [activeSlide]: true }))}
                                        onLoadedData={(e) => {
                                            // Ensure video plays
                                            e.target.play().catch(() => {});
                                        }}
                                    >
                                        <source src={slides[activeSlide].src} type="video/mp4" />
                                    </video>
                                ) : (
                                    failedImageIdx[activeSlide] ? (
                                        <div className="w-full h-full bg-gradient-to-br from-gray-900 via-gray-700 to-gray-800 flex items-center justify-center">
                                            <div className="text-center">
                                                <Shield className="h-10 w-10 text-indigo-400 mx-auto" />
                                                <p className="mt-2 text-indigo-100">Media unavailable. Explore the platform below.</p>
                                            </div>
                                        </div>
                                    ) : (
                                        <img
                                            src={slides[activeSlide].poster || slides[activeSlide].src}
                                            className="w-full h-full object-cover"
                                            alt="Hero"
                                            loading="eager"
                                            onError={() => setFailedImageIdx((prev) => ({ ...prev, [activeSlide]: true }))}
                                        />
                                    )
                                )}
                            </div>
                            <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent" />
                            <div className="absolute bottom-0 left-0 p-6">
                                <h1 className="text-3xl sm:text-4xl font-bold text-white">
                                    {slides[activeSlide].title}
                                </h1>
                                <p className="mt-2 text-indigo-100 max-w-2xl">
                                    {slides[activeSlide].subtitle}
                                </p>
                            </div>
                            {/* Slider arrows */}
                            <button
                                className="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/40 text-white hover:bg-black/60"
                                onClick={() => setActiveSlide((i) => (i - 1 + slides.length) % slides.length)}
                                aria-label="Previous slide"
                            >
                                <ChevronLeft className="h-5 w-5" />
                            </button>
                            <button
                                className="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/40 text-white hover:bg-black/60"
                                onClick={() => setActiveSlide((i) => (i + 1) % slides.length)}
                                aria-label="Next slide"
                            >
                                <ChevronRight className="h-5 w-5" />
                            </button>
                            {/* Slider controls */}
                            <div className="absolute bottom-4 right-4 flex items-center gap-2">
                                {slides.map((_, idx) => (
                                    <button
                                        key={idx}
                                        className={`h-2 w-2 rounded-full ${activeSlide === idx ? 'bg-white' : 'bg-white/50'}`}
                                        onClick={() => setActiveSlide(idx)}
                                        aria-label={`Slide ${idx + 1}`}
                                    />
                                ))}
                            </div>
                        </div>

                        {/* Search: keywords, country, category */}
                        <form onSubmit={goSearch} className="mt-6 bg-white shadow rounded-xl p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by keywords"
                                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <select
                                value={country}
                                onChange={(e) => setCountry(e.target.value)}
                                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">World</option>
                                {countries.map((c) => (
                                    <option key={c} value={c}>{tCountry(c)}</option>
                                ))}
                            </select>
                            <select
                                value={category}
                                onChange={(e) => setCategory(e.target.value)}
                                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                {categoryOptions.map((opt) => (
                                    <option key={`cat-${opt.value}`} value={opt.value}>{opt.label}</option>
                                ))}
                            </select>
                            <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Search
                            </button>
                        </form>
                    </div>
                </section>


                {/* Enhanced Stats Section with Animated Counters */}
                <section className="py-16 bg-gradient-to-br from-indigo-50 via-white to-blue-50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-gray-900 mb-2">
                                Platform Statistics
                            </h2>
                            <p className="text-gray-600">
                                Join thousands of businesses and professionals worldwide
                            </p>
                        </div>
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            {statsList.map((stat, index) => {
                                const AnimatedCounter = () => {
                                    const [count, setCount] = React.useState(0);
                                    const [hasAnimated, setHasAnimated] = React.useState(false);
                                    const counterRef = React.useRef(null);

                                    React.useEffect(() => {
                                        if (hasAnimated) return;

                                        const observer = new IntersectionObserver(
                                            (entries) => {
                                                if (entries[0].isIntersecting && !hasAnimated) {
                                                    const duration = 2000;
                                                    const startTime = Date.now();
                                                    const startValue = 0;
                                                    const endValue = stat.value;
                                                    
                                                    const animate = () => {
                                                        const now = Date.now();
                                                        const progress = Math.min((now - startTime) / duration, 1);
                                                        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                                                        const current = Math.floor(startValue + (endValue - startValue) * easeOutQuart);
                                                        
                                                        setCount(current);
                                                        
                                                        if (progress < 1) {
                                                            requestAnimationFrame(animate);
                                                        } else {
                                                            setHasAnimated(true);
                                                        }
                                                    };

                                                    animate();
                                                    observer.disconnect();
                                                }
                                            },
                                            { threshold: 0.1 }
                                        );

                                        if (counterRef.current) {
                                            observer.observe(counterRef.current);
                                        }

                                        return () => observer.disconnect();
                                    }, [stat.value, hasAnimated]);

                                    return (
                                        <div ref={counterRef} id={`stat-${index}`} className="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                                            <div className={`${stat.bgColor} rounded-lg p-4 inline-flex mb-4`}>
                                                <stat.icon className={`h-8 w-8 ${stat.color}`} />
                                            </div>
                                            <div className={`text-4xl font-bold ${stat.color} mb-2`}>
                                                {formatNumber(count)}{stat.value >= 1000000 ? '+' : stat.value >= 1000 ? '+' : ''}
                                            </div>
                                            <div className="text-gray-600 font-medium text-sm">
                                                {stat.label}
                                            </div>
                                        </div>
                                    );
                                };

                                return <AnimatedCounter key={index} />;
                            })}
                        </div>
                    </div>
                </section>

                {/* Latest Products */}
                <section className="py-16 bg-white">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-2xl font-bold text-gray-900">Latest Products</h2>
                            <Link href="/marketplace" className="text-indigo-600 hover:text-indigo-800">View all</Link>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            {latestProducts.map((p) => (
                                <div key={p.id} className="bg-white shadow rounded-lg overflow-hidden">
                                    {p.photo ? (
                                        <img src={p.photo} alt={p.title} className="h-40 w-full object-cover" />
                                    ) : (
                                        <div className="h-40 w-full bg-gray-100 flex items-center justify-center text-gray-400">No Image</div>
                                    )}
                                    <div className="p-4">
                                        <h3 className="text-lg font-semibold text-gray-900 truncate">{p.title}</h3>
                                        <div className="mt-2 text-gray-600 text-sm">{p.location || '—'}</div>
                                        <div className="mt-2 font-bold text-gray-900">{p.currency} {p.price ?? '—'}</div>
                                        <div className="mt-4">
                                            <Link href={`/listings/${p.id}`} className="text-indigo-600 hover:text-indigo-800">View</Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Latest Auctions */}
                <section className="py-16 bg-gray-50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-2xl font-bold text-gray-900">Latest Auctions</h2>
                            <Link href="/auctions" className="text-indigo-600 hover:text-indigo-800">View all</Link>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {latestAuctions.map((a) => (
                                <div key={a.id} className="bg-white shadow rounded-lg overflow-hidden">
                                    <div className="p-4">
                                        <h3 className="text-lg font-semibold text-gray-900 truncate">{a.title}</h3>
                                        <div className="mt-2 text-gray-600 text-sm">{a.location || '—'}</div>
                                        <div className="mt-2 text-sm">Ends in: <span className="font-mono">{timeLeft(a.ends_at_ts)}</span></div>
                                        <div className="mt-4">
                                            <Link href={`/auctions/${a.id}`} className="text-indigo-600 hover:text-indigo-800">View</Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="py-16 bg-white">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {features.map((feature, index) => (
                                <div key={index} className="bg-white shadow rounded-lg p-6">
                                    <feature.icon className="h-8 w-8 text-indigo-600" />
                                    <h3 className="mt-4 text-lg font-semibold text-gray-900">{feature.title}</h3>
                                    <p className="mt-2 text-gray-600">{feature.description}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Testimonials Section */}
                <section className="py-16 bg-gray-50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {[
                                { name: 'Sarah Chen', role: 'Procurement Manager', quote: 'Power helped us find reliable suppliers quickly.' },
                                { name: 'Miguel Santos', role: 'Founder', quote: 'Our sales grew 220% after joining the marketplace.' },
                                { name: 'Aisha Khan', role: 'HR Lead', quote: 'We hired top talent in days using Jobs.' }
                            ].map((t, idx) => (
                                <div key={idx} className="bg-white shadow rounded-lg p-6">
                                    <Star className="h-6 w-6 text-yellow-500" />
                                    <p className="mt-4 text-gray-700">"{t.quote}"</p>
                                    <div className="mt-4 text-gray-900 font-semibold">{t.name}</div>
                                    <div className="text-gray-600">{t.role}</div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="py-12 bg-gray-900">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 text-gray-300">
                            <div>
                                <h4 className="text-white font-semibold">About Power</h4>
                                <p className="mt-2">Global business platform connecting companies, talent, and opportunities.</p>
                            </div>
                            <div>
                                <h4 className="text-white font-semibold">Resources</h4>
                                <ul className="mt-2 space-y-2">
                                    <li><Link href="/marketplace" className="hover:text-white">Marketplace</Link></li>
                                    <li><Link href="/tenders" className="hover:text-white">Tenders</Link></li>
                                    <li><Link href="/jobs" className="hover:text-white">Jobs</Link></li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="text-white font-semibold">Contact</h4>
                                <p className="mt-2">support@power.example</p>
                            </div>
                        </div>
                        <div className="mt-8 text-center text-gray-500">© {new Date().getFullYear()} Power, Inc.</div>
                    </div>
                </footer>
            </div>
    );
}