import Image from 'next/image';

export default function AboutSection() {
  return (
    <section className="py-12 md:py-16 bg-white">
      <div className="container-custom">
        {/* Section label */}
        <div className="mb-4">
          <span className="text-[#62bb46] font-semibold text-sm uppercase tracking-wider">
            О Компании
          </span>
        </div>

        {/* Main heading */}
        <h2 className="text-2xl md:text-3xl lg:text-4xl font-bold text-[#3d4543] mb-10 max-w-4xl">
          Мебельная компания Е1 это крупное современное производство
        </h2>

        <div className="flex flex-col lg:flex-row gap-8 lg:gap-12 items-center">
          {/* Text content - left side */}
          <div className="flex-1 space-y-5 text-gray-600 leading-relaxed">
            <p>
              Мы на своём опыте убедились, что когда людям нужен порядок дома, они ищут простое и доступное решение, поэтому в 2007-м году запустили серийное производство стандартных шкафов-купе. Мы постоянно совершенствовали конструкцию, сокращали сроки производства и предлагали рынку всё новые модели.
            </p>
            <p>
              Наше производство оснащено качественными станками Shelling, IMA, Hirzt, Biesse, Filato производства Италии и Германии. Многие процессы автоматизированы – это существенно повышает качество продукции.
            </p>
            <p>
              Мы уверены в качестве продукции, поэтому даем гарантию до 10 лет (в зависимости от типа шкафа). Мы эксперты в системах хранения, и производим серийные шкафы, шкафы на заказ, встроенные системы хранения и гардеробные.
            </p>
          </div>

          {/* Image - right side */}
          <div className="flex-1 w-full lg:w-auto">
            <div className="relative aspect-[4/3] w-full rounded-xl overflow-hidden shadow-lg">
              <Image
                src="/images/o-fabrik.jpg"
                alt="Производство мебельной компании Е1"
                fill
                className="object-cover"
                sizes="(max-width: 1024px) 100vw, 50vw"
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
